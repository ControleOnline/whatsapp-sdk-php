<?php

namespace ControleOnline\WhatsApp;

use ControleOnline\WhatsApp\Messages\WhatsAppMediaInterface;
use ControleOnline\WhatsApp\Messages\WhatsAppMessageInterface;
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

    public function sendMessage(WhatsAppMessageInterface $message)
    {
        $message->validate();
        $response = self::$client->post("/messages/" . $message->getOriginNumber() . "/text", [
            'json' => [
                'number' => $message->getDestinationNumber(),
                'message' => $message->getMessage()
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function sendMedia(WhatsAppMediaInterface $message)
    {
        $message->validate();
        $response = self::$client->post("/messages/" . $message->getOriginNumber() . "/media", [
            'multipart' => [
                'number' => $message->getDestinationNumber(),
                'caption' => $message->getMessage(),
                'file' => $message->getFileContent()
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
