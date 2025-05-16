<?php

namespace NotificationChannels\HyperSender;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use NotificationChannels\HyperSender\Exceptions\CouldNotSendNotification;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HyperText.
 */
class Whatsapp
{
    /** HyperSender API Base URI.*/
    protected const API_BASE_URI = 'https://app.hypersender.com/api/whatsapp/v1';

    public function __construct(
        protected ?string $instanceId = null,
        protected ?string $token = null,
        protected HttpClient $http = new HttpClient,
        protected ?string $apiBaseUri = null
    ) {
        $this->setApiBaseUri($apiBaseUri ?? static::API_BASE_URI);
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
     * Set HTTP Client.
     *
     * @return $this
     */
    public function setHttpClient(HttpClient $http): self
    {
        $this->http = $http;

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
     * Send text message.
     *
     * <code>
     * $params = [
     *   'chat_id'                  => '',
     *   'text'                     => '',
     *   'reply_to'      => '',
     *   'link_preview'             => '',
     * ];
     * </code>
     *
     *
     * @throws CouldNotSendNotification
     */
    public function sendMessage(array $params, bool $safe = false): ?ResponseInterface
    {
        $endpoint = $safe ? 'send-text-safe' : 'send-text';

        return $this->sendRequest($endpoint, $params, $safe);
    }

    /**
     * Send File as Image or Document.
     *
     *
     * @throws CouldNotSendNotification
     */
    public function sendFile(array $params, string $type, bool $multipart = false): ?ResponseInterface
    {
        return $this->sendRequest('send'.Str::studly($type), $params, $multipart);
    }

    /**
     * Send a Poll.
     *
     *
     * @throws CouldNotSendNotification
     */
    public function sendPoll(array $params): ?ResponseInterface
    {
        return $this->sendRequest('sendPoll', $params);
    }

    /**
     * Send a Contact.
     *
     *
     * @throws CouldNotSendNotification
     */
    public function sendContact(array $params): ?ResponseInterface
    {
        return $this->sendRequest('sendContact', $params);
    }

    /**
     * Get updates.
     *
     *
     * @throws CouldNotSendNotification
     */
    public function getUpdates(array $params): ?ResponseInterface
    {
        return $this->sendRequest('getUpdates', $params);
    }

    /**
     * Send a Location.
     *
     *
     * @throws CouldNotSendNotification
     */
    public function sendLocation(array $params): ?ResponseInterface
    {
        return $this->sendRequest('sendLocation', $params);
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
    protected function sendRequest(string $endpoint, array $params, bool $multipart = false): ?ResponseInterface
    {
        if (blank($this->token)) {
            throw CouldNotSendNotification::hyperSenderTokenNotProvided('You must provide your hypersender token to make any API requests.');
        }

        $apiUri = sprintf('%s/bot%s/%s', $this->apiBaseUri, $this->token, $endpoint);

        try {
            return $this->httpClient()->post($apiUri, [
                $multipart ? 'multipart' : 'form_params' => $params,
            ]);
        } catch (ClientException $exception) {
            throw CouldNotSendNotification::hyperSenderRespondedWithAnError($exception);
        } catch (Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithHyperSender($exception);
        }
    }
}
