<?php



namespace ControleOnline\WhatsApp\Session;

use ControleOnline\WhatsApp\Profile\WhatsAppProfile;

class WhatsSession
{

    private WhatsAppProfile $profile;
    private bool $online;
    private array $webhooks;

}