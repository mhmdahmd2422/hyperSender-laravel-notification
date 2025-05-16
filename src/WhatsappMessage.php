<?php

declare(strict_types=1);

namespace NotificationChannels\HyperSender;

use Illuminate\Support\Facades\View;
use JsonException;
use NotificationChannels\HyperSender\Contracts\HyperSenderContract;
use NotificationChannels\HyperSender\Exceptions\CouldNotSendNotification;
use Psr\Http\Message\ResponseInterface;

final class WhatsappMessage extends WhatsappBase implements HyperSenderContract
{
    public function __construct(
        string $content = '',
    ) {
        parent::__construct();
        $this->content($content);
    }

    public static function create(string $content = ''): self
    {
        return new self($content);
    }

    public function content(string $content, ?int $limit = null): self
    {
        $this->payload['text'] = $content;

        return $this;
    }

    public function line(string $content): self
    {
        $this->payload['text'] .= "$content\n";

        return $this;
    }

    public function lineIf(bool $condition, string $line): self
    {
        return $condition ? $this->line($line) : $this;
    }

    public function escapedLine(string $content): self
    {
        $content = str_replace('\\', '\\\\', $content);

        $escapedContent = preg_replace_callback(
            '/[_*[\]()~`>#\+\-=|{}.!]/',
            fn ($matches): string => "\\$matches[0]",
            $content
        );

        return $this->line($escapedContent ?? $content);
    }

    public function view(string $view, array $data = [], array $mergeData = []): self
    {
        return $this->content(View::make($view, $data, $mergeData)->render());
    }

    /**
     * @return array<int, array<string, mixed>>|ResponseInterface|null
     *
     * @throws CouldNotSendNotification
     * @throws JsonException
     */
    public function send(): array|ResponseInterface|null
    {
        return $this->whatsapp->sendMessage($this->toArray());
    }
}
