<?php

namespace NotificationChannels\HyperSender\Exceptions;

use Exception;
use Illuminate\Http\Client\RequestException;
use JsonException;

/**
 * Class CouldNotSendNotification.
 */
final class CouldNotSendNotification extends Exception
{
    /**
     * Thrown when there's a bad request and an error is responded.
     *
     * @throws JsonException
     */
    public static function hyperSenderRespondedWithAnError(RequestException $exception): self
    {
        if (! $exception->response) {
            return new self('hyperSender responded with an error but no response body found');
        }

        $statusCode = $exception->response->status();
        $description = $exception->response->getReasonPhrase();

        $reasons = self::extractErrorReasons($exception->response->body());

        return new self("hyperSender responded with an error `{$statusCode} - {$description} - {$reasons}`", 0, $exception);
    }

    private static function extractErrorReasons(string $responseBody): string
    {
        $result = json_decode($responseBody);
        $errors = [];

        // Check for 'errors' object
        if (isset($result->errors) && is_object($result->errors)) {
            foreach ($result->errors as $fieldErrors) {
                if (is_array($fieldErrors)) {
                    $errors = array_merge($errors, $fieldErrors);
                }
            }
        }
        // Check for 'message' string
        if (isset($result->message) && is_string($result->message)) {
            $errors[] = $result->message;
        }
        return implode(' ', $errors) ?: 'No reasons given';
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
}
