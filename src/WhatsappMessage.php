<?php

declare(strict_types=1);

namespace NotificationChannels\HyperSender;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\View;
use JsonException;
use NotificationChannels\HyperSender\Contracts\HyperSenderContract;
use NotificationChannels\HyperSender\Exceptions\CouldNotSendNotification;
use Psr\Http\Message\ResponseInterface;

final class WhatsappMessage extends WhatsappBase implements HyperSenderContract
{
    private const CHUNK_SEPARATOR = '%#TGMSG#%';
    private int $chunkSize = 4096; // Default chunk size
    private bool $shouldChunk;


    public function __construct(string $content = '') {
        parent::__construct();

        $this->content($content);
        $this->setReplyTo();
        $this->setLinkPreview();
    }

    public function setReplyTo(string $replyTo = 'string'): self
    {
        $this->payload['reply_to'] = $replyTo;

        return $this;
    }

    public function setLinkPreview(bool $linkPreview = false): self
    {
        $this->payload['link_preview'] = $linkPreview;

        return $this;
    }

    public static function create(string $content = ''): self
    {
        return new self($content);
    }

    public function safeMode(bool $safeMode = true): self
    {
        $this->whatsapp->setSafeMode($safeMode);

        return $this;
    }

    public function content(string $content, bool $chunk = true): self
    {
        $this->shouldChunk = $chunk;
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

    public function setChunkSize(): self
    {
        $this->chunkSize = config('hyper-sender.whatsapp.chunk_size', 0) > 0 ? config('hyper-sender.whatsapp.chunk_size') : $this->chunkSize;

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>|ResponseInterface|null
     *
     * @throws CouldNotSendNotification
     * @throws JsonException
     */
    public function send(): array|Response|null
    {
        if ($this->shouldChunk) {
            $this->setChunkSize();
            return $this->sendChunkedMessage($this->toArray());
        }

        return $this->whatsapp->sendMessage($this->toArray());
    }


    /**
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     *
     * @throws CouldNotSendNotification
     * @throws JsonException
     */
    private function sendChunkedMessage(array $params): array
    {
        $messages = $this->chunkStrings($params['text'], $this->chunkSize);

        return Collection::make($messages)
            ->filter()
            ->map(function (string $text, int $index) use ($params) {
                $payload = [...$params, 'text' => $text];

                $response = $this->whatsapp->sendMessage($payload);
                sleep(1); // Rate limiting

                return $response ? json_decode(
                    $response->getBody()->getContents(),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                ) : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function chunkStrings(string $value, int $limit): array
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return [$value];
        }

        $output = explode(self::CHUNK_SEPARATOR, wordwrap($value, $limit, self::CHUNK_SEPARATOR));

        return count($output) <= 1
            ? mb_str_split($value, $limit, 'UTF-8')
            : $output;
    }
}
