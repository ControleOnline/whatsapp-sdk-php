<?php

namespace ControleOnline\WhatsApp\Messages;

interface WhatsAppMediaInterface extends WhatsAppMessageInterface
{
    public function getFileContent(): string;
}
