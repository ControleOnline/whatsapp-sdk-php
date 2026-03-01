<?php

namespace ControleOnline\WhatsApp\Messages;

use ControleOnline\Messages\MediaInterface;
use GuzzleHttp\Client;

class WhatsAppMedia implements MediaInterface
{

    private string $type;
    private array $data;


    public function getType(): string
    {
        return $this->type;
    }


    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }


    public function getData(): array
    {
        return $this->data;
    }


    public function setData(array $data): self
    {
        $this->data = $data;

        $this->setType(''); // @todo Detectar

        return $this;
    }

    public function fromFile(string $path): self
    {
        if (!file_exists($path)) {
            throw new \Exception("Arquivo não encontrado: {$path}");
        }

        $this->data = [
            'name'     => 'file',
            'contents' => fopen($path, 'rb'),
            'filename' => basename($path)
        ];

        $this->type = mime_content_type($path) ?: 'application/octet-stream';

        return $this;
    }

    public function fromUrl(string $url): self
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception("URL inválida: {$url}");
        }

        $client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36'
            ],
            'allow_redirects' => true,
            'timeout' => 30
        ]);

        $response = $client->get($url);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Falha ao baixar arquivo. Status: " . $response->getStatusCode());
        }

        $stream = $response->getBody();

        $filename = basename(parse_url($url, PHP_URL_PATH));

        $this->data = [
            'name'     => 'file',
            'contents' => $stream,
            'filename' => $filename
        ];

        $this->type = $response->getHeaderLine('Content-Type') ?: 'application/octet-stream';

        return $this;
    }
}
