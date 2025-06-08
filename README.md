
# HyperSender Notification Channel for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-notification-channels/hypersender.svg?style=flat-square)](https://packagist.org/packages/mhmdahmd/hypersender)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-notification-channels/hypersender.svg?style=flat-square)](https://packagist.org/packages/mhmdahmd/hypersender)

A Laravel notification channel for sending WhatsApp messages through the HyperSender API. This package seamlessly integrates with Laravel's notification system, allowing you to send WhatsApp messages to your users with a clean, fluent API.

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Available Methods](#available-methods)
- [Exception Handling](#exception-handling)
- [License](#license)

## Installation

You can install the package via composer:

```bash
composer require mhmdahmd/hypersender
```

## Configuration

Add your HyperSender API credentials to your `config/services.php` file:

```php
'hypersender-api' => [
    'token' => env('HYPERSENDER_API_TOKEN'),
    'instance_id' => env('HYPERSENDER_INSTANCE_ID'),
    'api_base_uri' => env('HYPERSENDER_API_BASE_URI', 'https://app.hypersender.com/api/whatsapp/v1'),
],
```

Then add these environment variables to your `.env` file:

```
HYPERSENDER_API_TOKEN=your-api-token
HYPERSENDER_INSTANCE_ID=your-instance-id
```

## Usage

To use this package, you need to create a notification class in your Laravel application. Here's an example:

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\HyperSender\WhatsappMessage;

class WhatsappNotification extends Notification
{
    public function via($notifiable)
    {
        return ['whatsapp'];
    }

    public function toWhatsapp($notifiable)
    {
        return WhatsappMessage::create()
            ->to($notifiable->phone_number)
            ->content('Hello from HyperSender! This is a test message.');
    }
}
```

In your notifiable model (typically the User model), add the `routeNotificationForWhatsapp` method:

```php
public function routeNotificationForWhatsapp()
{
    return $this->phone_number; // Return the user's phone number in international format (e.g., '201234567890')
}
```

Then, you can send a notification to a user:

```php
$user->notify(new WhatsappNotification());
```

## Available Methods

### WhatsappMessage

The `WhatsappMessage` class provides a fluent interface for creating WhatsApp messages:

```php
WhatsappMessage::create('Your message content')
    ->to('201234567890') // Phone number in international format
    ->line('Add a new line')
    ->escapedLine('Escaped content with *special* _characters_')
    ->lineIf($condition, 'Conditional line')
    ->safeMode() // Enable safe mode
    ->setLinkPreview(true) // Enable link previews
    ->token('custom-token') // Override default token
    ->sendWhen($condition) // Send only when condition is true
    ->onError(function ($error) {
        // Handle errors
    });
```

### Message Formatting

- **Basic Content**: `content('Your message')`
- **Add Lines**: `line('New line of text')`
- **Escaped Content**: `escapedLine('Text with *markdown* that will be escaped')`
- **Conditional Lines**: `lineIf($condition, 'This line appears only if condition is true')`
- **View Rendering**: `view('notifications.whatsapp.alert', ['data' => $data])`

### Message Options

- **Safe Mode**: `safeMode()` - Enables safe mode for message sending
- **Link Preview**: `setLinkPreview(true)` - Controls link preview behavior
- **Custom Token**: `token('your-custom-token')` - Override the default API token
- **Conditional Sending**: `sendWhen($condition)` - Only send when condition is true
- **Custom Options**: `options(['custom_option' => 'value'])` - Add custom options to the payload

### Message Chunking

Large messages are automatically split into smaller chunks to comply with WhatsApp limits. The default chunk size is 4096 characters, but you can customize this in your configuration.

## Exception Handling

You can handle exceptions using the `onError` method:

```php
WhatsappMessage::create('Your message')
    ->to('201234567890')
    ->onError(function ($error) {
        Log::error('WhatsApp notification failed', $error);
        // Additional error handling logic
    });
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.