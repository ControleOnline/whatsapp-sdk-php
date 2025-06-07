<?php



namespace ControleOnline\WhatsApp\Messages;

use ControleOnline\Messages\ContentInterface;
use ControleOnline\Messages\MediaInterface;

class WhatsAppContent implements ContentInterface
{

    private string $mediaType;
    private string $body;
    private ?MediaInterface $media = null;


    public function getMediaType(): string
    {
        return $this->mediaType;
    }


    public function setMediaType(string $mediaType): self
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }


    public function setBody(string $body): self
    {
        $this->body = $body;

        $this->setMediaType('audio'); // @todo Detectar

        return $this;
    }

    public function getMedia(): ?MediaInterface
    {
        return $this->media;
    }


    public function setMedia(MediaInterface $media): self
    {
        $this->media = $media;

        return $this;
    }
}
