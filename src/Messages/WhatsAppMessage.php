<?php

namespace ControleOnline\WhatsApp\Messages;

class WhatsAppMessage
{
    private int $origin_number;
    private int $destination_number;
    private string $message_id;
    private WhatsAppContent $message_content;


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

    public function getMessageId(): string
    {
        return $this->message_id;
    }

    public function setMessageId(string $message_id): self
    {
        $this->message_id = $message_id;

        return $this;
    }

    public function getMessageContent(): WhatsAppContent
    {
        return $this->message_content;
    }

    public function setMessageContent(WhatsAppContent $message_content): self
    {
        $this->message_content = $message_content;

        return $this;
    }
}
