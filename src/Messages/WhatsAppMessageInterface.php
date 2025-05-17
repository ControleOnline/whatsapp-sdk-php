<?php

namespace ControleOnline\WhatsApp\Messages;

interface WhatsAppMessageInterface
{
    public function getOriginNumber(): int;
    public function getDestinationNumber(): int;
    public function getMessage(): string;
    public function setOriginNumber(int $origin_number): self;
    public function setDestinationNumber(int $destination_number): self;
    public function setMessage(string $messge): self;
}
