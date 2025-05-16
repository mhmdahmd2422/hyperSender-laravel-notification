<?php

namespace NotificationChannels\HyperSender;

use JsonSerializable;
use NotificationChannels\HyperSender\Traits\HasSharedLogic;

/**
 * Class WhatsappBase.
 */
class WhatsappBase implements JsonSerializable
{
    use HasSharedLogic;

    public Whatsapp $whatsapp;

    public function __construct()
    {
        $this->whatsapp = app(Whatsapp::class);
    }
}
