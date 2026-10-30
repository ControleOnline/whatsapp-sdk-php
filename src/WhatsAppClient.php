<?php

/*
 * Contract imported from AGENTS.md
 * ## Escopo
 * - SDK especifico de WhatsApp.
 * - Implementa cliente, perfis e mensagens de WhatsApp sobre os contratos genericos de `messages-sdk`.
 *
 * ## Quando usar
 * - Prompts sobre envio, modelagem ou adaptacao de mensagem de WhatsApp e manutencao do cliente desse canal.
 *
 * ## Limites
 * - A regra de integracao orquestrada e de webhook continua em `integration`.
 * - Este modulo deve focar no SDK e nos modelos especificos do WhatsApp.
 */

namespace ControleOnline\WhatsApp;

use ControleOnline\WhatsApp\Messages\WhatsAppContent;
use ControleOnline\WhatsApp\Messages\WhatsAppMedia;
use ControleOnline\WhatsApp\Messages\WhatsAppMessage;
use ControleOnline\WhatsApp\Profile\WhatsAppProfile;
use GuzzleHttp\Client;

/**
 * Dual transport WhatsApp client.
 *
 * - unofficial (default): REST against Baileys/api-whatsapp (WHATSAPP_SERVER).
 * - official: Meta WhatsApp Cloud API (Graph) called directly from PHP.
 */
class WhatsAppClient
{
    public const PROVIDER_UNOFFICIAL = 'unofficial';
    public const PROVIDER_OFFICIAL = 'official';

    private Client $http;
    private string $provider;
    private string $phoneNumberId;
    private string $apiVersion;

    /**
     * @param string $baseUrl   Unofficial: WHATSAPP_SERVER. Official: graph host (default graph.facebook.com).
     * @param string $apiKey    Unofficial: Bearer for Baileys API. Official: Meta access token.
     * @param string $provider  unofficial|official
     * @param string $phoneNumberId  Required when provider=official (Meta phone_number_id).
     * @param string $apiVersion Graph API version when official (default v22.0).
     */
    public function __construct(
        string $baseUrl,
        string $apiKey,
        string $provider = self::PROVIDER_UNOFFICIAL,
        string $phoneNumberId = '',
        string $apiVersion = 'v22.0'
    ) {
        $this->provider = $provider === self::PROVIDER_OFFICIAL
            ? self::PROVIDER_OFFICIAL
            : self::PROVIDER_UNOFFICIAL;
        $this->phoneNumberId = trim($phoneNumberId);
        $this->apiVersion = $apiVersion !== '' ? $apiVersion : 'v22.0';

        if ($this->provider === self::PROVIDER_OFFICIAL) {
            $host = $baseUrl !== '' ? rtrim($baseUrl, '/') : 'https://graph.facebook.com';
            $this->http = new Client([
                'base_uri' => $host . '/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'http_errors' => false,
            ]);
        } else {
            $this->http = new Client([
                'base_uri' => rtrim($baseUrl, '/') . '/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'http_errors' => false,
            ]);
        }
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function isOfficial(): bool
    {
        return $this->provider === self::PROVIDER_OFFICIAL;
    }

    public function createSession(WhatsAppProfile $whatsAppProfile): array
    {
        if ($this->isOfficial()) {
            return [
                'success' => true,
                'provider' => self::PROVIDER_OFFICIAL,
                'message' => 'Official Cloud API does not use QR session; credentials are stored on the connection.',
                'phone' => $whatsAppProfile->getPhoneNumber(),
            ];
        }

        $response = $this->http->post('sessions/start', [
            'json' => [
                'phone' => $whatsAppProfile->getPhoneNumber(),
            ],
        ]);

        return $this->decodeResponse($response);
    }

    public function send(WhatsAppMessage $message): array
    {
        $message->validate();

        if ($message->getMessageContent()->getMedia()) {
            return $this->sendMedia($message);
        }

        return $this->sendMessage($message);
    }

    public function sendMedia(WhatsAppMessage $message): array
    {
        $message->validate();

        if ($this->isOfficial()) {
            return $this->sendOfficialMedia($message);
        }

        $mediaData = $message->getMessageContent()->getMedia()->getData();

        $response = $this->http->post('messages/' . $message->getOriginNumber(), [
            'multipart' => [
                [
                    'name' => 'number',
                    'contents' => $message->getDestinationNumber(),
                ],
                [
                    'name' => 'message',
                    'contents' => $message->getMessageContent()->getBody(),
                ],
                $mediaData,
            ],
        ]);

        return $this->decodeResponse($response);
    }

    private function sendMessage(WhatsAppMessage $message): array
    {
        if ($this->isOfficial()) {
            return $this->sendOfficialText($message);
        }

        $response = $this->http->post('messages/' . $message->getOriginNumber(), [
            'json' => [
                'number' => $message->getDestinationNumber(),
                'message' => $message->getMessageContent()->getBody(),
            ],
        ]);

        return $this->decodeResponse($response);
    }

    private function sendOfficialText(WhatsAppMessage $message): array
    {
        $this->assertOfficialReady();

        $to = $this->normalizeOfficialTo($message->getDestinationNumber());
        $body = (string) $message->getMessageContent()->getBody();

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $body,
            ],
        ];

        $response = $this->http->post(
            $this->apiVersion . '/' . $this->phoneNumberId . '/messages',
            ['json' => $payload]
        );

        return $this->mapOfficialResponse($response);
    }

    private function sendOfficialMedia(WhatsAppMessage $message): array
    {
        $this->assertOfficialReady();

        $to = $this->normalizeOfficialTo($message->getDestinationNumber());
        $media = $message->getMessageContent()->getMedia();
        $mediaData = $media ? $media->getData() : null;
        $caption = (string) $message->getMessageContent()->getBody();

        $link = null;
        $mime = 'application/octet-stream';
        $filename = 'file';

        if (is_array($mediaData)) {
            $link = $mediaData['url'] ?? $mediaData['link'] ?? $mediaData['imageUrl']
                ?? $mediaData['videoUrl'] ?? $mediaData['audioUrl'] ?? $mediaData['documentUrl']
                ?? null;
            $mime = (string) ($mediaData['mime'] ?? $mediaData['mimetype'] ?? $mime);
            $filename = (string) ($mediaData['filename'] ?? $mediaData['name'] ?? $filename);
        } elseif (is_string($mediaData) && preg_match('#^https?://#i', $mediaData)) {
            $link = $mediaData;
        }

        if (!$link) {
            return [
                'success' => false,
                'provider' => self::PROVIDER_OFFICIAL,
                'error' => 'official_media_requires_public_url',
                'message' => 'Official Cloud API media send requires a public URL (imageUrl/videoUrl/documentUrl/link).',
            ];
        }

        $type = $this->guessOfficialMediaType($mime, $filename);
        $mediaPayload = ['link' => $link];
        if ($caption !== '' && in_array($type, ['image', 'video', 'document'], true)) {
            $mediaPayload['caption'] = $caption;
        }
        if ($type === 'document' && $filename !== '') {
            $mediaPayload['filename'] = $filename;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => $type,
            $type => $mediaPayload,
        ];

        $response = $this->http->post(
            $this->apiVersion . '/' . $this->phoneNumberId . '/messages',
            ['json' => $payload]
        );

        return $this->mapOfficialResponse($response);
    }

    public function getUnreadMessages(string $destination_number): array
    {
        if ($this->isOfficial()) {
            return [];
        }

        $response = $this->http->get('messages/' . $destination_number . '/unread');
        $data = $this->decodeResponse($response);

        if (!is_array($data)) {
            return [];
        }

        $messages = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            $media = null;
            if (isset($item['content']['file'])) {
                $media = (new WhatsAppMedia())
                    ->setData($item['content']['file']['data']);
            }

            $content = new WhatsAppContent();
            $content->setBody($item['content']['body'] ?? '');

            if ($media) {
                $content->setMedia($media);
            }

            $message = new WhatsAppMessage();
            $message->setMessageId($item['messageid'] ?? '')
                ->setOriginNumber($item['remoteJid'] ?? '')
                ->setDestinationNumber($destination_number)
                ->setMessageContent($content);

            $messages[] = $message;
        }

        return $messages;
    }

    private function assertOfficialReady(): void
    {
        if ($this->phoneNumberId === '') {
            throw new \InvalidArgumentException(
                'Official WhatsApp Cloud API requires phone_number_id on the connection credentials.'
            );
        }
    }

    private function normalizeOfficialTo(string $number): string
    {
        return preg_replace('/\D+/', '', $number) ?: $number;
    }

    private function guessOfficialMediaType(string $mime, string $filename): string
    {
        $mime = strtolower($mime);
        $filename = strtolower($filename);

        if (strpos($mime, 'image/') === 0 || preg_match('/\.(jpe?g|png|webp|gif)$/', $filename)) {
            return 'image';
        }
        if (strpos($mime, 'video/') === 0 || preg_match('/\.(mp4|3gp)$/', $filename)) {
            return 'video';
        }
        if (strpos($mime, 'audio/') === 0 || preg_match('/\.(ogg|mp3|aac|amr)$/', $filename)) {
            return 'audio';
        }

        return 'document';
    }

    private function mapOfficialResponse($response): array
    {
        $decoded = $this->decodeResponse($response);
        $status = method_exists($response, 'getStatusCode') ? (int) $response->getStatusCode() : 0;

        if ($status >= 200 && $status < 300 && is_array($decoded)) {
            $messageId = $decoded['messages'][0]['id'] ?? null;

            return [
                'success' => true,
                'provider' => self::PROVIDER_OFFICIAL,
                'message_id' => $messageId,
                'raw' => $decoded,
            ];
        }

        $errorMessage = is_array($decoded)
            ? ($decoded['error']['message'] ?? $decoded['message'] ?? 'Meta Cloud API error')
            : 'Meta Cloud API error';

        return [
            'success' => false,
            'provider' => self::PROVIDER_OFFICIAL,
            'http_status' => $status,
            'error' => 'meta_api_error',
            'message' => $errorMessage,
            'raw' => is_array($decoded) ? $decoded : null,
        ];
    }

    private function decodeResponse($response): array
    {
        $body = method_exists($response, 'getBody') ? (string) $response->getBody() : '';
        if ($body === '') {
            return [];
        }
        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
    }
}
