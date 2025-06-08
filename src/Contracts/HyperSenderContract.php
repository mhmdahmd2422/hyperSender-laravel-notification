<?php

namespace NotificationChannels\HyperSender\Contracts;

use Illuminate\Http\Client\Response;
use NotificationChannels\HyperSender\Exceptions\CouldNotSendNotification;

interface HyperSenderContract
{
    /**
     * Send the message.
     *
     *
     * @throws CouldNotSendNotification
     */
    public function send(): Response|array|null;
}
