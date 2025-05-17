<?php

namespace ControleOnline\WhatsApp;

use ControleOnline\WhatsApp\Messages\WhatsAppContent;
use ControleOnline\WhatsApp\Messages\WhatsAppMedia;
use ControleOnline\WhatsApp\Messages\WhatsAppMessage;
use ControleOnline\WhatsApp\Session\WhatsappSession;
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

    public function sendMessage(WhatsAppMessage $message)
    {
        $message->validate();
        $response = self::$client->post("/messages/" . $message->getOriginNumber() . "/text", [
            'json' => [
                'number' =>  $message->getDestinationNumber(),
                'message' => $message->getMessageContent()->getBody()
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function sendMedia(WhatsAppMessage $message)
    {
        $message->validate();
        $response = self::$client->post("/messages/" . $message->getOriginNumber() . "/media", [
            'multipart' => [
                'number' => $message->getDestinationNumber(),
                'message' => $message->getMessageContent()->getBody(),
                'file' => $message->getMessageContent()->getMedia()->getData()
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getQrCode(string $destination_number)
    {

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
