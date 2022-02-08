<?php

namespace Alexsmirnovdotcom\Odata\Tests\Unit;

use Alexsmirnovdotcom\Odata\Exceptions\ODataServiceException;
use Alexsmirnovdotcom\Odata\Exceptions\Request\AuthException;
use Alexsmirnovdotcom\Odata\Exceptions\Request\NotFoundException;
use Alexsmirnovdotcom\Odata\Exceptions\Request\RequestException;
use Alexsmirnovdotcom\Odata\Exceptions\Service\ConnectionException;
use Alexsmirnovdotcom\Odata\OData;
use Alexsmirnovdotcom\Odata\ODataRequestExceptionHandler;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Alexsmirnovdotcom\Odata\ODataRequestExceptionHandler
 */
class ExceptionHandlerTest extends TestCase
{
    protected ODataRequestExceptionHandler $handler;
    protected OData $odata;

    public function setUp(): void
    {
        $this->handler = new ODataRequestExceptionHandler();
        $this->odata = $this->createMock(OData::class);

    }

    /**
     * @dataProvider providerFor_test_handleGuzzleRequestException
     * @covers \Alexsmirnovdotcom\Odata\ODataRequestExceptionHandler::handle()
     */
    public function test_handle_GuzzleRequestException(int $code, string $expectedExceptionClass): void
    {
        $fakeResponse = new Response($code, [], null, '1.1', 'Reason phrase');
        $requestMock = $this->createMock(\Psr\Http\Message\RequestInterface::class);
        $exception = new GuzzleRequestException('', $requestMock, $fakeResponse);

        $this->expectException($expectedExceptionClass);
        $this->handler->handle($exception, $this->odata);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\ODataRequestExceptionHandler::handle()
     */
    public function test_handle_GuzzleConnectException(): void
    {
        $this->expectException(ConnectionException::class);
        $guzzleExceptionMock = $this->createMock(ConnectException::class);
        $this->handler->handle($guzzleExceptionMock, $this->odata);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\ODataRequestExceptionHandler::handle()
     */
    public function test_handle_GuzzleException(): void
    {
        $this->expectException(ODataServiceException::class);
        $guzzleExceptionMock = $this->createMock(GuzzleException::class);
        $this->handler->handle($guzzleExceptionMock, $this->odata);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\ODataRequestExceptionHandler::handle()
     */
    public function test_handle_otherExceptionsThrowsUp(): void
    {
        $this->expectException(\Exception::class);
        $this->handler->handle(new \Exception(), $this->odata);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\ODataRequestExceptionHandler::handle()
     */
    public function providerFor_test_handleGuzzleRequestException(): array
    {
        return [
            'Unauthorized' => [401, AuthException::class],
            'Not Found' => [404, NotFoundException::class],
            'Other error codes (420)' => [420, RequestException::class],
            'Other error codes (500)' => [500, RequestException::class],
            'Other error codes (300)' => [300, RequestException::class],
        ];
    }
}