<?php

namespace Alexsmirnovdotcom\Odata;

use Alexsmirnovdotcom\Odata\Exceptions\Service\IllegalKeyOffsetException;
use Alexsmirnovdotcom\Odata\Interfaces\ResponseInterface;

class Response implements ResponseInterface
{
    protected bool $error;
    protected int $code;
    protected string $message;
    protected string $body;

    public function __construct(bool $error, int $code = 0, string $message = '', string $body = '')
    {
        $this->error = $error;
        $this->code = $code;
        $this->message = $message;
        $this->body = $body;
    }

    public function isFailed(): bool
    {
        return $this->error;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getBody($key = null)
    {
        if (is_callable($key)) {
            return $key($this->body);
        }

        $body = json_decode($this->body, true, 512, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        if (!is_null($key) && !array_key_exists($key, $body)) {
            $message = "Key '{$key}' does not exists in this response body.";
            throw new IllegalKeyOffsetException($this, $message);
        }
        return !is_null($key) ? $body[$key] : $body;
    }

    public function jsonSerialize(): array
    {
        return [
            'error' => $this->error,
            'code' => $this->code,
            'message' => $this->message,
            'body' => $this->body,
        ];
    }
}
