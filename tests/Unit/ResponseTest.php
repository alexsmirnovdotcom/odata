<?php

namespace Alexsmirnovdotcom\Odata\Tests\Unit;

use Alexsmirnovdotcom\Odata\Exceptions\Service\IllegalKeyOffsetException;
use Alexsmirnovdotcom\Odata\Response;

/**
 * @covers \Alexsmirnovdotcom\Odata\Response
 */
class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Alexsmirnovdotcom\Odata\Response::getBody()
     */
    public function test_getBodyWithoutPassedKeyMustReturnArrayFromJson(): void
    {
        $body = '{"body": 10}';
        $response = new Response(false, 0, '', $body);
        $decodedBodyFromJson = json_decode($body, true);
        $this->assertEquals($decodedBodyFromJson, $response->getBody());
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\Response::getBody()
     */
    public function test_getBodyWithPassedKeyMustReturnArrayFromJson(): void
    {
        $body = '{"body": 10, "value": [{"id": 1}, {"id": 2}]}';
        $response = new Response(false, 0, '', $body);
        $decodedBodyFromJson = json_decode($body, true);
        $this->assertEquals($decodedBodyFromJson['value'], $response->getBody('value'));
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\Response::getBody()
     */
    public function test_getBodyWithPassedInvalidKeyMustThrowException(): void
    {
        $body = '{"body": 10, "value": [{"id": 1}, {"id": 2}]}';
        $response = new Response(false, 0, '', $body);
        $this->expectException(IllegalKeyOffsetException::class);
        $response->getBody('invalid');
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\Response::getBody()
     */
    public function test_getBodyWithPassedCallbackAsKeyMustReturnResultOfCallback(): void
    {
        $body = '{"body": 10, "value": [{"id": 1}, {"id": 2}]}';
        $response = new Response(false, 0, '', $body);
        $callbackResult = $response->getBody(fn($body) => json_decode($body, true)['value']);
        $decodedBodyFromJson = json_decode($body, true)['value'];
        self::assertEquals($decodedBodyFromJson, $callbackResult);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\Response::jsonSerialize()
     */
    public function test_JsonSerialaze(): void
    {
        $body = '{"body": 10, "value": [{"id": 1}, {"id": 2}]}';
        $response = new Response(false, 0, '', $body);

        $expectedArray = [
            'error' => $response->isFailed(),
            'code' => $response->getCode(),
            'message' => $response->getMessage(),
            'body' => $body,
        ];
        $responseAsJson = json_encode($response);

        $this->assertEquals(json_encode($expectedArray), $responseAsJson);
    }
}