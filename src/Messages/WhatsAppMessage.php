<?php

namespace ControleOnline\WhatsApp\Messages;

use ControleOnline\Messages\ContentInterface;
use ControleOnline\Messages\MessageInterface;

class WhatsAppMessage implements MessageInterface
{
    private int $origin_number;
    private string $destination_number;
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


    public function getDestinationNumber(): string
    {
        return $this->destination_number;
    }


    public function setDestinationNumber(string $destination_number): self
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

    public function getMessageContent(): ContentInterface
    {
        return $this->message_content;
    }

    public function setMessageContent(ContentInterface $message_content): self
    {
        $this->message_content = $message_content;

        return $this;
    }
}
