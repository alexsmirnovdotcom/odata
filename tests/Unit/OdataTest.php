<?php

namespace Alexsmirnovdotcom\Odata\Tests\Unit;

use Alexsmirnovdotcom\Odata\Config;
use Alexsmirnovdotcom\Odata\Constants\Methods;
use Alexsmirnovdotcom\Odata\Exceptions\Service\InvalidParameterException;
use Alexsmirnovdotcom\Odata\Interfaces\ConfigInterface;
use Alexsmirnovdotcom\Odata\Interfaces\QueryParametersInterface;
use Alexsmirnovdotcom\Odata\Interfaces\RequestExceptionHandlerInterface;
use Alexsmirnovdotcom\Odata\Interfaces\ResponseInterface;
use Alexsmirnovdotcom\Odata\Interfaces\ServiceInterface;
use Alexsmirnovdotcom\Odata\OData;
use Alexsmirnovdotcom\Odata\ODataRequestExceptionHandler;
use Alexsmirnovdotcom\Odata\QueryParameters;
use Alexsmirnovdotcom\Odata\Response;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Alexsmirnovdotcom\Odata\OData
 */
class OdataTest extends TestCase
{
    protected Client $client;

    protected ConfigInterface $config;

    protected QueryParametersInterface $parameters;

    protected RequestExceptionHandlerInterface $exceptionsHandler;

    protected OData $odata;

    /**
     * @covers \Alexsmirnovdotcom\Odata\OData::__construct()
     */
    public function test_constructWithoutPassedPops(): void
    {
        $odata = new $this->odata;
        $this->assertInstanceOf(OData::class, $odata);
        $this->assertInstanceOf(Client::class, $this->odata->client);
        $this->assertInstanceOf(ConfigInterface::class, $this->odata->config);
        $this->assertInstanceOf(QueryParameters::class, $this->odata->parameters);
        $this->assertInstanceOf(RequestExceptionHandlerInterface::class, $this->odata->exceptionHandler);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\OData::__construct()
     */
    public function test_constructWithPassedPops(): void
    {
        $client = $this->createMock(Client::class);
        $config = $this->createMock(Config::class);
        $parameters = $this->createMock(QueryParameters::class);
        $exceptionsHandler = $this->createMock(ODataRequestExceptionHandler::class);

        $this->odata = new $this->odata($config, $client, $parameters, $exceptionsHandler);

        $this->assertInstanceOf(ServiceInterface::class, $this->odata);
        $this->assertSame($client, $this->odata->client);
        $this->assertSame($config, $this->odata->config);
        $this->assertSame($parameters, $this->odata->parameters);
        $this->assertSame($exceptionsHandler, $this->odata->exceptionHandler);
    }

    public function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->config = $this->createMock(Config::class);
        $this->parameters = $this->createMock(QueryParameters::class);
        $this->exceptionsHandler = $this->createMock(ODataRequestExceptionHandler::class);

        $this->odata = new class(
            $this->config,
            $this->client,
            $this->parameters,
            $this->exceptionsHandler
        ) extends OData {
            public Config $config;
            public Client $client;
            public QueryParameters $parameters;
            public RequestExceptionHandlerInterface $exceptionHandler;

            protected function request(string $method): Response
            {
                return new Response(false, 200, '', '');
            }
        };
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\OData::authAs()
     */
    public function test_authAs(): void
    {
        $auth = ['login', 'password'];
        $this->config
            ->expects($this->once())
            ->method('setAuth')
            ->with($auth);

        $result = $this->odata->authAs($auth);
        $this->assertInstanceOf(ServiceInterface::class, $result);

    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\OData::resource()
     */
    public function test_resource(): void
    {
        $resource = 'resource';
        $this->parameters
            ->expects($this->once())
            ->method('setResource')
            ->with($resource);

        $result = $this->odata->resource($resource);
        $this->assertInstanceOf(ServiceInterface::class, $result);

    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\OData::from()
     */
    public function test_from(): void
    {
        $resource = 'resource';
        $this->parameters
            ->expects($this->once())
            ->method('setResource')
            ->with($resource);

        $result = $this->odata->resource($resource);
        $this->assertInstanceOf(ServiceInterface::class, $result);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\OData::getOnlyCount()
     */
    public function test_getOnlyCount(): void
    {
        $this->parameters
            ->expects($this->once())
            ->method('setTrueIsOnlyCount');

        $result = $this->odata->getOnlyCount();
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\OData::get()
     */
    public function test_get_WithoutPassedId(): void
    {
        $this->parameters
            ->expects($this->never())
            ->method('resetOnly')
            ->with('filter', 'data');

        $this->parameters
            ->expects($this->never())
            ->method('setGuid');

        $result = $this->odata->get();
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\OData::get()
     */
    public function test_get_WithPassedValidId(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $this->parameters
            ->expects($this->once())
            ->method('resetOnly')
            ->with('filter', 'data');

        $this->parameters
            ->expects($this->once())
            ->method('setGuid')
            ->with($id);

        $result = $this->odata->get($id);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\OData::get()
     */
    public function test_get_WithPassedInvalidIdMustThrowException(): void
    {
        $id = '00000000-0000-0000-0000-00000000000z';

        $this->parameters
            ->expects($this->once())
            ->method('resetOnly')
            ->with('filter', 'data');

        $this->parameters
            ->expects($this->once())
            ->method('setGuid')
            ->with($id)
            ->willThrowException(new InvalidParameterException());

        $this->expectException(InvalidParameterException::class);
        $this->odata->get($id);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\OData::request()
     */
    public function test_request(): void
    {
        $response = $this->createMock(\GuzzleHttp\Psr7\Response::class);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response->expects($this->once())
            ->method('getReasonPhrase')
            ->willReturn('OK');

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($this->createMock(StreamInterface::class));

        $this->config
            ->expects($this->once())
            ->method('getConfigParameters')
            ->willReturn([]);

        $this->parameters
            ->expects($this->once())
            ->method('getQueryAndBody')
            ->willReturn([]);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->withAnyParameters()
            ->willReturn($response);

        $odata = new class($this->config, $this->client, $this->parameters, $this->exceptionsHandler) extends OData {
            public function request(string $method): ResponseInterface
            {
                return parent::request($method);
            }

            protected function compileURI(): string
            {
                return '';
            }
        };

        $result = $odata->request(Methods::GET);
        $this->assertInstanceOf(ResponseInterface::class, $result);

        $this->expectException(InvalidParameterException::class);
        $this->exceptionsHandler
            ->expects($this->once())
            ->method('handle')
            ->withAnyParameters()
            ->willThrowException(new InvalidParameterException());
        $odata->request('invalid');
    }

    /**
     * @covers       \Alexsmirnovdotcom\Odata\OData::compileURI()
     * @dataProvider providerFor_test_compileURI
     */
    public function test_compileURI(string $host, string $resource, string $guid, bool $isOnlyCount, string $expectedResult): void
    {

        $odata = new class($this->config, $this->client, $this->parameters, $this->exceptionsHandler) extends OData {
            public function compileURI(): string
            {
                return parent::compileURI();
            }
        };

        $hostIsEmpty = empty($host);
        $invokeTimes = $hostIsEmpty ? $this->never() : $this->any();

        $this->config
            ->expects($this->once())
            ->method('getHost')
            ->willReturn($host);

        $this->parameters
            ->expects($invokeTimes)
            ->method('getResource')
            ->willReturn($resource);

        $this->parameters
            ->expects($invokeTimes)
            ->method('getGuid')
            ->willReturn($guid);


        $invokeMethodTimes = empty($guid) ? $this->any() : $this->never();
        $this->parameters
            ->expects($invokeMethodTimes)
            ->method('isOnlyCount')
            ->willReturn($isOnlyCount);

        if ($hostIsEmpty) {
            $this->expectException(InvalidParameterException::class);
        }

        $result = $odata->compileURI();
        $this->assertIsString($result);
        $this->assertEquals($expectedResult, $result);
    }

    public function providerFor_test_compileURI(): array
    {
        return [
            [
                'host',
                'resource',
                "(guid'00000000-0000-0000-0000-000000000000')",
                false,
                "host/odata/standard.odata/resource(guid'00000000-0000-0000-0000-000000000000')/"]
            ,
            [
                'host',
                'resource',
                "(guid'00000000-0000-0000-0000-000000000000')",
                true,
                "host/odata/standard.odata/resource(guid'00000000-0000-0000-0000-000000000000')/"
            ],
            [
                'host',
                'resource',
                '',
                true,
                "host/odata/standard.odata/resource/\$count"
            ],
            [
                '',
                'resource',
                '',
                true,
                "host/odata/standard.odata/resource/\$count"
            ],
        ];
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\OData::debug()
     */
    public function test_debug(): void
    {
        $config = [
            'auth' => ['login', 'password'],
            'headers' => ['Accept' => 'application/json']
        ];

        $parameters = [
            '$filter' => 'СуммаДокумента ge 1000'
        ];

        $this->config
            ->expects($this->once())
            ->method('getConfigParameters')
            ->willReturn($config);

        $this->parameters
            ->expects($this->once())
            ->method('getQueryAndBody')
            ->willReturn($parameters);

        $expects = [
            'URI' => 'URI',
            'Config' => $config,
            'QueryParameters' => $parameters,
        ];

        $odata = new class($this->config, $this->client, $this->parameters, $this->exceptionsHandler) extends OData {
            public function compileURI(): string
            {
                return 'URI';
            }
        };

        $result = $odata->debug();
        $this->assertIsArray($result);
        $this->assertEquals($expects, $result);
    }

    /**
     * @covers       \Alexsmirnovdotcom\Odata\OData::create()
     * @dataProvider providerFor_test_create
     */
    public function test_create(array $data): void
    {
        $this->parameters
            ->expects($this->once())
            ->method('resetExclude')
            ->with('resource');

        $this->parameters
            ->expects($this->once())
            ->method('clearGuid');

        $this->parameters
            ->expects($this->once())
            ->method('setData')
            ->with($data);

        $result = $this->odata->create($data);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * @covers       \Alexsmirnovdotcom\Odata\OData::update()
     * @dataProvider providerFor_test_update
     */
    public function test_update(string $id, array $data): void
    {
        $isValidGuid = $this->isValidGuid($id);

        $this->parameters
            ->expects($this->once())
            ->method('resetExclude')
            ->with('resource');

        $this->parameters
            ->expects($this->once())
            ->method('setGuid')
            ->with($id);

        $this->parameters
            ->expects($isValidGuid ? $this->once() : $this->never())
            ->method('setData')
            ->with($data);

        if (!$isValidGuid) {
            $this->parameters
                ->expects($this->once())
                ->method('setGuid')
                ->with($id)
                ->willThrowException(new InvalidParameterException());

            $this->expectException(InvalidParameterException::class);
        }

        $result = $this->odata->update($id, $data);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    protected function isValidGuid($guid): bool
    {
        preg_match('/[a-fA-F\d]{8}(?:-[a-fA-F\d]{4}){3}-[a-fA-F\d]{12}/m', $guid, $match);

        if (!isset($match[0])) {
            return false;
        }

        return true;
    }

    public function providerFor_test_update(): array
    {
        return [
            ['', ['Контрагент_Key' => '00000000-0000-0000-0000-000000000001']],
            ['00000000-0000-0000-0000-000000000001', ['Контрагент_Key' => '00000000-0000-0000-0000-000000000000']],
            ['00000000-0000-0000-0000-asdasdas ', ['Контрагент_Key' => '00000000-0000-0000-0000-000000000000']],
            ['00000000-0000-0000-0000-000000000001', []],
            ['00000000-0000-0000-0000-00000000asdas0z', []],
            ['', []],
        ];
    }

    public function providerFor_test_create(): array
    {
        return [
            [['Контрагент_Key' => '00000000-0000-0000-0000-000000000001']],
            [[]],
        ];
    }

    /**
     * @covers       \Alexsmirnovdotcom\Odata\OData::markDeleted()
     * @dataProvider providerFor_test_markDeleted
     */
    public function test_markDeleted(string $id, string $deletionMarkKey = ''): void
    {
        $isValidGuid = $this->isValidGuid($id);
        $deletionMarkKeyExpects = empty($deletionMarkKey) ? 'DeletionMark' : $deletionMarkKey;

        $this->parameters
            ->expects($this->once())
            ->method('resetExclude')
            ->with('resource');

        $this->parameters
            ->expects($this->once())
            ->method('setGuid')
            ->with($id);

        $this->parameters
            ->expects($isValidGuid ? $this->once() : $this->never())
            ->method('setData')
            ->with([$deletionMarkKeyExpects => true]);

        if (!$isValidGuid) {
            $this->parameters
                ->expects($this->once())
                ->method('setGuid')
                ->with($id)
                ->willThrowException(new InvalidParameterException());

            $this->expectException(InvalidParameterException::class);
        }

        $result = $this->odata->markDeleted($id, $deletionMarkKey);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function providerFor_test_markDeleted(): array
    {
        return [
            [''],
            ['00000000-0000-0000-0000-000000000000'],
            ['00000000-0000-0000-0000-000000000000', 'Deleted'],
            ['00000000-0000-0000-0000asd'],
            ['00000000-0000-0000-0000asd', 'Deleted'],
            ['00000000-0000-0000-0000asd', ''],
        ];
    }

    /**
     * @covers       \Alexsmirnovdotcom\Odata\OData::forceDelete()
     * @dataProvider providerFor_test_markDeleted
     */
    public function test_forceDelete(string $guid): void
    {
        $this->parameters
            ->expects($this->once())
            ->method('resetExclude');

        $this->parameters
            ->expects($this->once())
            ->method('setGuid')
            ->with($guid);

        if (!$this->isValidGuid($guid)) {
            $this->parameters
                ->method('setGuid')
                ->willThrowException(new InvalidParameterException());

            $this->expectException(InvalidParameterException::class);
        }

        $result = $this->odata->forceDelete($guid);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }


    public function providerFor_test_forceDelete(): array
    {
        return [
            [''],
            ['00000000-0000-0000-0000-000000000000'],
            ['00000000-0000-0000-0000asd'],
        ];
    }
}