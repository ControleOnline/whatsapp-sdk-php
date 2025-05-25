<?php

namespace ControleOnline\WhatsApp\Messages;

use ControleOnline\Messages\MediaInterface;

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
}
