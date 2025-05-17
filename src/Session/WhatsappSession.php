<?php



namespace ControleOnline\WhatsApp\Session;

use ControleOnline\WhatsApp\Profile\WhatsAppProfile;

class WhatsappSession
{

    private WhatsAppProfile $profile;
    private bool $online;
    private array $webhooks;

}