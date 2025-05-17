<?php

namespace ControleOnline\WhatsApp\Messages;

class WhatsAppMedia
{

    private string $type;
    private array $data;


    public function getType(): string
    {
        return $this->type;
    }


    private function setType(string $type): self
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
