<?php

use ControleOnline\WhatsApp\WhatsAppClient;
use ControleOnline\WhatsApp\Messages\WhatsAppContent;
use ControleOnline\WhatsApp\Messages\WhatsAppMessage;
use ControleOnline\WhatsApp\Profile\WhatsAppProfile;
use PHPUnit\Framework\TestCase;

class WhatsAppClientOfficialTest extends TestCase
{
    public function testDefaultProviderIsUnofficial(): void
    {
        $client = new WhatsAppClient('http://localhost', 'key');
        $this->assertSame(WhatsAppClient::PROVIDER_UNOFFICIAL, $client->getProvider());
        $this->assertFalse($client->isOfficial());
    }

    public function testOfficialProviderFlag(): void
    {
        $client = new WhatsAppClient(
            'https://graph.facebook.com',
            'token',
            WhatsAppClient::PROVIDER_OFFICIAL,
            '123456'
        );
        $this->assertTrue($client->isOfficial());
        $this->assertSame(WhatsAppClient::PROVIDER_OFFICIAL, $client->getProvider());
    }

    public function testOfficialCreateSessionDoesNotHitQrFlow(): void
    {
        $client = new WhatsAppClient(
            'https://graph.facebook.com',
            'token',
            WhatsAppClient::PROVIDER_OFFICIAL,
            '123456'
        );
        $profile = new WhatsAppProfile();
        $profile->setPhoneNumber('5511999999999');
        $result = $client->createSession($profile);
        $this->assertTrue($result['success']);
        $this->assertSame(WhatsAppClient::PROVIDER_OFFICIAL, $result['provider']);
    }

    public function testOfficialSendRequiresPhoneNumberId(): void
    {
        $client = new WhatsAppClient(
            'https://graph.facebook.com',
            'token',
            WhatsAppClient::PROVIDER_OFFICIAL,
            ''
        );
        $content = new WhatsAppContent();
        $content->setBody('hello');
        $message = new WhatsAppMessage();
        $message->setAction('sendMessage');
        $message->setOriginNumber('5511000000000');
        $message->setDestinationNumber('5511999999999');
        $message->setMessageContent($content);

        $this->expectException(\InvalidArgumentException::class);
        $client->send($message);
    }
}
