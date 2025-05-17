<?php

namespace ControleOnline\WhatsApp;

use ControleOnline\WhatsApp\Messages\WhatsAppMedia;
use ControleOnline\WhatsApp\Messages\WhatsAppMessage;
use GuzzleHttp\Client;

class WhatsAppClient
{
    private static $client;
    public function __construct(
        $baseUrl,
        $apiKey
    ) {
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

        $response = self::$client->post("/messages/" . $message->getOriginNumber() . "/text", [
            'json' => [
                'number' => $message->getDestinationNumber(),
                'message' => $message->getMessage()
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function sendMedia(WhatsAppMedia $message)
    {

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
