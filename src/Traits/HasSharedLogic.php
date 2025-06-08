<?php

declare(strict_types=1);

namespace NotificationChannels\HyperSender\Traits;

use Closure;
use Illuminate\Support\Traits\Conditionable;

/**
 * Trait HasSharedLogic
 *
 * Provides shared functionality for WhatsApp message handling.
 */
trait HasSharedLogic
{
    use Conditionable;

    /** @var string|null Bot Token */
    public ?string $token = null;

    /** @var array<string, mixed> Params payload */
    protected array $payload = [];

    /** @var bool|null Condition for sending the message */
    private ?bool $sendCondition = null;

    /** @var Closure|null Callback function to handle exceptions */
    public ?Closure $exceptionHandler = null;

    /**
     * Set the recipient's WhatsApp phone number.
     *
     * @param string $phoneNumber The recipient's phone number in international format (e.g., '201234567890')
     */
    public function to(string $phoneNumber): static
    {
        $this->payload['chatId'] = $phoneNumber . '@c.us';

        return $this;
    }

    /**
     * Set the Bot Token. Overrides default bot token with the given value for this notification.
     *
     * @param  string  $token  The bot token to use
     */
    public function token(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Determine if bot token is given for this notification.
     */
    public function hasToken(): bool
    {
        return $this->token !== null;
    }

    /**
     * Set additional options to pass to sendMessage method.
     *
     * @param  array<string, mixed>  $options  Additional options
     */
    public function options(array $options): static
    {
        $this->payload = [...$this->payload, ...$options];

        return $this;
    }

    /**
     * Registers a callback function to handle exceptions.
     *
     * This method allows you to define a custom error handler,
     * which will be invoked if an exception occurs during the
     * notification process. The callback must be a valid Closure.
     *
     * @param  Closure  $callback  The closure that will handle exceptions.
     */
    public function onError(Closure $callback): self
    {
        $this->exceptionHandler = $callback;

        return $this;
    }

    /**
     * Set a condition for sending the message.
     *
     * @param  bool|callable  $condition  The condition to evaluate
     */
    public function sendWhen(bool|callable $condition): static
    {
        $this->sendCondition = $this->when($condition, fn () => true, fn () => false);

        return $this;
    }

    /**
     * Determine if the message can be sent based on the condition.
     */
    public function canSend(): bool
    {
        return $this->sendCondition ?? true;
    }

    /**
     * Determine if chat id is not given.
     */
    public function toNotGiven(): bool
    {
        return ! isset($this->payload['chatId']);
    }

    /**
     * Get payload value for given key.
     *
     * @param  string  $key  The key to retrieve from payload
     * @return mixed The value from payload or null if not found
     */
    public function getPayloadValue(string $key): mixed
    {
        return $this->payload[$key] ?? null;
    }

    /**
     * Get the complete payload as array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
