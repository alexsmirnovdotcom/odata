<?php

namespace Alexsmirnovdotcom\Odata\Tests\Unit;

use Alexsmirnovdotcom\Odata\Config;
use Alexsmirnovdotcom\Odata\Interfaces\ConfigInterface;

/**
 * @covers \Alexsmirnovdotcom\Odata\Config
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Alexsmirnovdotcom\Odata\Config::__construct()
     * @return void
     */
    public function test__constructWithEmptyParams(): void
    {
        $config = new Config();
        $this->assertInstanceOf(ConfigInterface::class, $config);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\Config::__construct()
     * @return void
     */
    public function test__constructSetHeaders(): void
    {
        $config = new class extends Config {
            public array $headers;
        };
        $this->assertInstanceOf(ConfigInterface::class, $config);
        $defaultHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        $this->assertEquals($defaultHeaders, $config->headers);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\Config::__construct()
     * @return void
     */
    public function test__constructWithParams(): void
    {
        $config = new class extends Config {
            public array $headers;
            public array $auth;
            public array $clientParameters;
            public string $host;
        };

        $test = new $config('host');
        $this->assertInstanceOf(ConfigInterface::class, $test);
        $this->assertEquals('host', $test->host);

        $test = new $config('', ['login', 'pass']);
        $this->assertInstanceOf(ConfigInterface::class, $test);
        $this->assertEquals(['login', 'pass'], $test->auth);


        $test = new $config('', [], ['Accept' => 'atom/xml']);
        $this->assertInstanceOf(ConfigInterface::class, $test);
        $defaultHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        $mergedHeaders = array_merge($defaultHeaders, ['Accept' => 'atom/xml']);
        $this->assertEquals($mergedHeaders, $test->headers);


        $test = new $config('', [], [], ['timeout' => 1]);
        $this->assertInstanceOf(ConfigInterface::class, $test);
        $this->assertEquals(['timeout' => 1], $test->clientParameters);

    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\Config::getConfigParameters()
     * @return void
     */
    public function test__getConfigParameters(): void
    {
        $defaultHeaders = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $additionalHeader = 'Content-Encoding';
        $additionalHeaderValue = 'test value';
        $additionalClientParameter = ['timeout' => 10];
        $auth = ['login', 'password'];

        $expected = array_merge([
            'auth' => $auth,
            'headers' => array_merge($defaultHeaders, [$additionalHeader => $additionalHeaderValue]),
        ], $additionalClientParameter);

        $config = new Config();
        $config
            ->setAuth($auth)
            ->setHeader($additionalHeader, $additionalHeaderValue)
            ->setClientParameters($additionalClientParameter);

        $this->assertEquals($expected, $config->getConfigParameters());
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\Config::clearHeader()
     * @return void
     */
    public function test_clearHeader(): void
    {
        $defaultHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];

        $config = new Config();

        $this->assertEquals($defaultHeaders, $config->getHeaders());
        $result = $config->clearHeader('Accept');
        unset($defaultHeaders['Accept']);
        $this->assertEquals($defaultHeaders, $config->getHeaders());

    }
}