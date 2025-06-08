<?php

namespace NotificationChannels\HyperSender;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

/**
 * Class HyperSenderServiceProvider.
 */
class HyperSenderServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->bind(Whatsapp::class, static fn () => new Whatsapp(
            apiBaseUri: config('services.hypersender-api.api_base_uri'),
            instanceId: config('services.hypersender-api.instance_id'),
            token: config('services.hypersender-api.token')
        ));

        Notification::resolved(static function (ChannelManager $service) {
            $service->extend('whatsapp', static fn ($app) => $app->make(WhatsappChannel::class));
        });
    }
}
