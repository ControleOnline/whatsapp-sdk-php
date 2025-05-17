<?php

namespace ControleOnline\WhatsApp;

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

    public function sendMessage(string $origin, array $message)
    {

        $response = self::$client->post("/messages/$origin/text", [
            'json' => $message,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function sendMedia(string $origin, array $message)
    {

        $response = self::$client->post("/messages/$origin/media", [
            'multipart' => $message,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
