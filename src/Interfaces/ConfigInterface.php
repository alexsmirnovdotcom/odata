<?php

namespace Alexsmirnovdotcom\Odata\Interfaces;

use Alexsmirnovdotcom\Odata\Config;

interface ConfigInterface
{
    public function __construct(
        string $host = '',
        array  $auth = [],
        array  $headers = [],
        array  $clientParameters = []
    );

    public function getHost(): string;

    /**
     * Устанавливает URI базы к которой будет производиться запрос.
     * @param string $host
     * @return ConfigInterface
     */
    public function setHost(string $host): ConfigInterface;

    public function getAuth(): array;

    /**
     * Устанавливает данные дла аутентификации.
     * @param array $auth
     * @return ConfigInterface
     */
    public function setAuth(array $auth): ConfigInterface;

    public function getHeaders(): array;

    /**
     * Устанавливает заголовки запроса.
     * Переданный массив полностью заменяет исходный.
     *
     * @param array $headers
     * @return ConfigInterface
     */
    public function setHeaders(array $headers): ConfigInterface;

    public function getConfigParameters(): array;

    /**
     * Устанавливает доп. параметры для запроса. Они передаются в Guzzle.
     * Например: ['timeout' => 10].
     *
     * @param array $clientParameters
     * @return Config
     */
    public function setClientParameters(array $clientParameters): Config;

    public function setHeader(string $header, string $value): Config;

    public function clearHeader(string $header): Config;
}
