<?php

namespace Alexsmirnovdotcom\Odata;

use Alexsmirnovdotcom\Odata\Interfaces\ConfigInterface;

class Config implements ConfigInterface
{
    protected string $host;
    protected array $auth;
    protected array $headers;
    protected array $clientParameters;
    protected array $defaultHeaders = ['Accept'=>'application/json', 'Content-Type' => 'application/json'];

    public function getConfigParameters(): array
    {
        $params = [
            'auth' => $this->getAuth(),
            'headers' => $this->getHeaders(),
        ];

        return array_merge_recursive($this->clientParameters, $params);
    }


    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): Config
    {
        $this->host = $host;
        return $this;
    }

    public function getAuth(): array
    {
        return $this->auth;
    }

    public function setAuth(array $auth): Config
    {
        $this->auth = $auth;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): Config
    {
        $this->headers = $headers;
        return $this;
    }

    public function setHeader(string $header, string $value): Config
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function clearHeader(string $header): Config
    {
        unset($this->headers[$header]);
        return $this;
    }

    public function __construct(string $host = '', array $auth = [], array $headers = [], array $clientParameters = [])
    {
        $this->auth = $auth;
        $this->host = $host;
        $this->headers = array_merge($this->defaultHeaders, $headers);
        $this->clientParameters = $clientParameters;
    }

    public function setClientParameters(array $clientParameters): Config
    {
        $this->clientParameters = $clientParameters;
        return $this;
    }
}
