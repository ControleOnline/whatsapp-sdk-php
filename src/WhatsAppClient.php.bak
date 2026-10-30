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

class WhatsAppClient
{
    private static $client;
    public function __construct($baseUrl, $apiKey)
    {
        if (! self::$client)
            self::$client = new Client([
                'base_uri' => $baseUrl,
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey
                ]
            ]);
    }


    public function createSession(WhatsAppProfile $whatsAppProfile)
    {
        $response = self::$client->post("/sessions/start", [
            'json' => [
                'phone' =>  $whatsAppProfile->getPhoneNumber()
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function send(WhatsAppMessage $message): array
    {
        $message->validate();

        if ($message->getMessageContent()->getMedia()) {
            return $this->sendMedia($message);
        }

        return $this->sendMessage($message);
    }

    private function sendMedia(WhatsAppMessage $message)
    {
        $mediaData = $message->getMessageContent()->getMedia()->getData();

        $response = self::$client->post("/messages/" . $message->getOriginNumber(), [
            'multipart' => [
                [
                    'name' => 'number',
                    'contents' => $message->getDestinationNumber()
                ],
                [
                    'name' => 'message',
                    'contents' => $message->getMessageContent()->getBody()
                ],
                $mediaData
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    private function sendMessage(WhatsAppMessage $message)
    {
        $response = self::$client->post("/messages/" . $message->getOriginNumber(), [
            'json' => [
                'number' =>  $message->getDestinationNumber(),
                'message' => $message->getMessageContent()->getBody()
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getUnreadMessages(string $destination_number): array
    {
        $response = self::$client->get("/messages/" . $destination_number . "/unread");
        $data = json_decode($response->getBody()->getContents(), true);

        $messages = [];
        foreach ($data as $item) {
            $media = null;
            if (isset($item['content']['file']))
                $media = (new WhatsAppMedia())
                    ->setData($item['content']['file']['data']);


            $content = new WhatsAppContent();
            $content->setBody($item['content']['body'] ?? '');

            if ($media)
                $content->setMedia($media);

            $message = new WhatsAppMessage();
            $message->setMessageId($item['messageid'])
                ->setOriginNumber($item['remoteJid'])
                ->setDestinationNumber($destination_number)
                ->setMessageContent($content);

            $messages[] = $message;
        }

        return $messages;
    }
}
