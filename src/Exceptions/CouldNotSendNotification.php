<?php

namespace NotificationChannels\HyperSender\Exceptions;

use Exception;
use GuzzleHttp\Exception\ClientException;
use JsonException;

/**
 * Class CouldNotSendNotification.
 */
final class CouldNotSendNotification extends Exception
{
    /**
     * Thrown when there's a bad request and an error is responded.
     *
     *
     * @throws JsonException
     */
    public static function hyperSenderRespondedWithAnError(ClientException $exception): self
    {
        if (! $exception->hasResponse()) {
            return new self('hyperSender responded with an error but no response body found');
        }

        $statusCode = $exception->getResponse()->getStatusCode();

        $result = json_decode($exception->getResponse()->getBody()->getContents());
        $description = $result->description ?? 'no description given';

        return new self("hyperSender responded with an error `{$statusCode} - {$description}`", 0, $exception);
    }

    /**
     * Thrown when there's no bot token provided.
     */
    public static function hyperSenderTokenNotProvided(string $message): self
    {
        return new self($message);
    }

    /**
     * Thrown when we're unable to communicate with Telegram.
     */
    public static function couldNotCommunicateWithHyperSender(string $message): self
    {
        return new self("The communication with hyperSender failed. `{$message}`");
    }

    /**
     * Thrown when the file cannot be opened.
     */
    public static function fileAccessFailed(string $file): self
    {
        return new self("Failed to open file: {$file}");
    }

    /**
     * Thrown when the file identifier is invalid (ID or URL).
     */
    public static function invalidFileIdentifier(string $file): self
    {
        return new self("Invalid file identifier: {$file}");
    }
}
