<?php

namespace ControleOnline\WhatsApp\Messages;

class WhatsAppMedia implements  WhatsAppMediaInterface
{
    private int $origin_number;
    private int $destination_number;
    private string $message;
    private string $file_content;



    public function validate(): self
    {
        return $this;
    }


    public function getOriginNumber(): int
    {
        return $this->origin_number;
    }


    public function setOriginNumber(int $origin_number): self
    {
        $this->origin_number = $origin_number;

        return $this;
    }


    public function getDestinationNumber(): int
    {
        return $this->destination_number;
    }


    public function setDestinationNumber(int $destination_number): self
    {
        $this->destination_number = $destination_number;

        return $this;
    }


    public function getMessage(): string
    {
        return $this->message;
    }


    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }


    public function getFileContent(): string
    {
        return $this->file_content;
    }


    public function setFileContent(string $file_content): self
    {
        $this->file_content = $file_content;

        return $this;
    }
}
