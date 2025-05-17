<?php



namespace ControleOnline\WhatsApp\Messages;

class WhatsAppContent
{

    private string $mediaType;
    private string $body;
    private WhatsAppMedia $media;


    public function getMediaType(): string
    {
        return $this->mediaType;
    }


    private function setMediaType(string $mediaType): self
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

    public function getMedia(): WhatsAppMedia
    {
        return $this->media;
    }


    public function setMedia(WhatsAppMedia $media): self
    {
        $this->media = $media;

        return $this;
    }
}
