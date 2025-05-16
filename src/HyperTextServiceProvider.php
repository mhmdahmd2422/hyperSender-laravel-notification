<?php

namespace NotificationChannels\HyperSender;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

/**
 * Class HyperTextServiceProvider.
 */
class HyperTextServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->bind(Whatsapp::class, static fn () => new Whatsapp(
            config('services.hypersender-api.instance_id'),
            config('services.hypersender-api.token'),
            app(HttpClient::class),
            config('services.hypersender-api.api_base_uri')
        ));

        Notification::resolved(static function (ChannelManager $service) {
            $service->extend('whatsapp', static fn ($app) => $app->make(WhatsappChannel::class));
        });
    }
}
