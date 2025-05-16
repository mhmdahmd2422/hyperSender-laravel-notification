<?php

namespace NotificationChannels\HyperSender\Contracts;

use NotificationChannels\HyperSender\Exceptions\CouldNotSendNotification;
use Psr\Http\Message\ResponseInterface;

interface HyperSenderContract
{
    /**
     * Send the message.
     *
     *
     * @throws CouldNotSendNotification
     */
    public function send(): ResponseInterface|array|null;
}
