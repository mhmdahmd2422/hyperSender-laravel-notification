<?php

namespace NotificationChannels\HyperSender;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use NotificationChannels\HyperSender\Exceptions\CouldNotSendNotification;

/**
 * Class Whatsapp.
 */
class Whatsapp
{
    /** HyperSender API Base URI.*/
    protected const API_BASE_URI = 'https://app.hypersender.com/api/whatsapp/v1';

    protected bool $safeMode = false;

    public function __construct(
        protected ?string $apiBaseUri = null,
        protected ?string $instanceId = null,
        protected ?string $token = null
    ) {
        $this->setApiBaseUri($apiBaseUri ?? static::API_BASE_URI);
        $this->setInstanceId($instanceId);
    }

    /**
     * Token getter.
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Token setter.
     *
     * @return $this
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * API Base URI getter.
     */
    public function getApiBaseUri(): string
    {
        return $this->apiBaseUri;
    }

    /**
     * API Base URI setter.
     *
     * @return $this
     */
    public function setApiBaseUri(string $apiBaseUri): self
    {
        $this->apiBaseUri = rtrim($apiBaseUri, '/');

        return $this;
    }

    /**
     * Instance ID getter.
     */
    public function setInstanceId(string $instanceId): self
    {
        $this->instanceId = $instanceId;

        return $this;
    }

    /**
     * Get Instance ID.
     */
    public function getInstanceId(): ?string
    {
        return $this->instanceId;
    }

    /**
     * Set safe mode.
     *
     * @return $this
     */
    public function setSafeMode(bool $safe = true): self
    {
        $this->safeMode = $safe;

        return $this;
    }

    /**
     * Get safe mode status.
     */
    public function isSafeMode(): bool
    {
        return $this->safeMode;
    }

    /**
     * Send text message.
     *
     * @throws CouldNotSendNotification
     */
    public function sendMessage(array $params): ?Response
    {
        $endpoint = $this->safeMode ? 'send-text-safe' : 'send-text';

        return $this->sendRequest(endpoint: $endpoint, params: $params);
    }

    /**
     * Get HttpClient.
     */
    protected function httpClient(): HttpClient
    {
        return $this->http;
    }

    /**
     * Send an API request and return response.
     *
     *
     * @throws CouldNotSendNotification
     */
    protected function sendRequest(string $endpoint, array $params, bool $multipart = false): ?Response
    {
        if (blank($this->token)) {
            throw CouldNotSendNotification::hyperSenderTokenNotProvided('You must provide your hypersender token to make any API requests.');
        }

        $apiUri = $this->getApiBaseUri() . '/' . $this->getInstanceId() . '/' . $endpoint;

           try {
               return Http::withToken($this->token)
                   ->contentType('application/json')
                   ->acceptJson()
                   ->post($apiUri, $params)
                   ->throw();
           } catch (ConnectionException $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithHyperSender($exception->getMessage());
           } catch (RequestException $exception) {
            throw CouldNotSendNotification::hyperSenderRespondedWithAnError($exception);
        }
    }
}
