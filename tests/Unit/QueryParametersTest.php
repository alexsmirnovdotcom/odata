<?php

namespace Alexsmirnovdotcom\Odata\Tests\Unit;

use Alexsmirnovdotcom\Odata\Constants\Inlinecount;
use Alexsmirnovdotcom\Odata\Constants\Metadata;
use Alexsmirnovdotcom\Odata\Exceptions\Service\InvalidParameterException;
use Alexsmirnovdotcom\Odata\QueryParameters;

/**
 * @covers \Alexsmirnovdotcom\Odata\QueryParameters
 */
class QueryParametersTest extends \PHPUnit\Framework\TestCase
{
    protected QueryParameters $parameters;

    public function setUp(): void
    {
        $this->parameters = new class extends QueryParameters {
            public string $resource = '';
            public string $guid = '';
            public string $filter = '';
            public string $select = '';
            public string $expand = '';
            public array $data = [];
            public ?int $skip = null;
            public ?int $top = null;
            public string $orderBy = '';
            public ?string $inlineCount = null;
            public bool $isOnlyCount = false;
            public string $format = '';
        };
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::getQueryAndBody()
     */
    public function test_getQueryAndBody(): void
    {
        $filterString = 'filter string';
        $data = ['key' => 'value'];

        $expects = [
            'query' => ['$filter' => $filterString],
            'body' => json_encode($data),
        ];

        $this->parameters->setFilter($filterString);
        $this->parameters->setData($data);
        $result = $this->parameters->getQueryAndBody();

        $this->assertIsArray($result);
        $this->assertEquals($expects, $result);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::getParameters()
     */
    public function test_getParameters(): void
    {
        $filterString = 'filter string';
        $expects = ['$filter' => $filterString];

        $this->parameters->setFilter($filterString);
        $result = $this->parameters->getParameters();
        $this->assertIsArray($result);
        $this->assertEquals($expects, $result);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::getData()
     */
    public function test_getDataMustReturnArrayWithEmptyOrStringOrNumericOrBool(): void
    {
        $callable = fn() => 'ok';

        $data = [
            'valBool' => true,
            'valInt' => 10,
            'valFloat' => 10.5,
            'valString' => 'ok',
            'valNull' => null,
            'valEmptyString' => '',
            'valCallback' => $callable,
            'valObject' => new \stdClass(),
        ];

        $expectsData = $data;
        unset($expectsData['valCallback'], $expectsData['valObject']);

        $this->parameters->setData($data);
        $result = $this->parameters->getData();
        $this->assertIsArray($result);
        $this->assertEquals($expectsData, $result);

    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setMetadataLevel()
     */
    public function test_setMetadataLevelWithValidValue(): void
    {
        $param = Metadata::FULL;
        $result = $this->parameters->setMetadataLevel($param);
        $this->assertInstanceOf(QueryParameters::class, $result);
        $this->assertEquals($param, $this->parameters->format);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setMetadataLevel()
     */
    public function test_setMetadataLevelWithInvalidValueMustThrowException()
    {
        $this->expectException(InvalidParameterException::class);
        $this->parameters->setMetadataLevel('invalid');
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setResource()
     */
    public function test_setResource(): void
    {
        $resource = ' resource ';
        $expects = trim($resource);

        $result = $this->parameters->setResource($resource);
        $this->assertInstanceOf(QueryParameters::class, $result);
        $this->assertEquals($expects, $this->parameters->resource);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::resetExclude()
     */
    public function test_resetExclude(): void
    {
        $guid = 'guid';
        $this->parameters->guid = $guid;
        $this->assertEquals($guid, $this->parameters->getGuid());

        $result = $this->parameters->resetExclude('guid');
        $this->assertInstanceOf(QueryParameters::class, $result);
        $this->assertEquals($guid, $this->parameters->getGuid());

        $this->parameters->resetExclude('resource');
        $this->assertNotEquals($guid, $this->parameters->getGuid());
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::resetOnly()
     */
    public function test_resetOnly(): void
    {
        $guid = 'guid';
        $filter = 'filter string';
        $this->parameters->guid = $guid;
        $this->parameters->filter = $filter;

        $this->assertEquals($guid, $this->parameters->getGuid());
        $this->assertEquals($filter, $this->parameters->filter);

        $result = $this->parameters->resetOnly('guid');

        $this->assertInstanceOf(QueryParameters::class, $result);
        $this->assertNotEquals($guid, $this->parameters->getGuid());
        $this->assertEquals($filter, $this->parameters->filter);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setGuid()
     */
    public function test_setGuidWithValidParameter(): void
    {
        $guid = '00000000-0000-0000-0000-000000000000';
        $result = $this->parameters->setGuid($guid);
        $guid = "(guid'{$guid}')";
        $this->assertInstanceOf(QueryParameters::class, $result);
        $this->assertEquals($guid, $this->parameters->getGuid());
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setGuid()
     */
    public function test_setGuidWithEmptyParameter(): void
    {
        $guid = '';
        $this->expectException(InvalidParameterException::class);
        $result = $this->parameters->setGuid($guid);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setGuid()
     */
    public function test_setGuidWithInvalidParameterMustThrowException(): void
    {
        $guid = '00000000-0000-0000-0000-00000000000z';
        $this->expectException(InvalidParameterException::class);
        $this->parameters->setGuid($guid);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setInlinecount()
     */
    public function test_setInlinecountWithValidParameter(): void
    {
        $param = Inlinecount::ALLPAGES;
        $result = $this->parameters->setInlinecount($param);
        $this->assertInstanceOf(QueryParameters::class, $result);
        $this->assertEquals($param, $this->parameters->inlineCount);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setInlinecount()
     */
    public function test_setInlinecountWithInvalidValueMustThrowException(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->parameters->setInlinecount('invalid');
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setTop()
     * @dataProvider providerForSkipAndTop
     */
    public function test_setTopMustSetValueGreaterThanZero(int $value, int $expects): void
    {
        $result = $this->parameters->setTop($value);
        $this->assertInstanceOf(QueryParameters::class, $result);
        $this->assertEquals($expects, $this->parameters->top);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setSkip()
     * @dataProvider providerForSkipAndTop
     */
    public function test_setSkipMustSetValueGreaterThanZero(int $value, int $expects): void
    {
        $result = $this->parameters->setSkip($value);
        $this->assertInstanceOf(QueryParameters::class, $result);
        $this->assertEquals($expects, $this->parameters->skip);
    }

    public function providerForSkipAndTop(): array
    {
        return [
            [10, 10],
            [-10, 0],
            [1, 1],
            [0, 0],
        ];
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setFilter()
     */
    public function test_setFilter(): void
    {
        $filter = ' СуммаДокумента ge 1000 ';
        $expects = trim($filter);

        $result = $this->parameters->setFilter($filter);
        $this->assertInstanceOf(QueryParameters::class, $result);
        $this->assertEquals($expects, $this->parameters->filter);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setSelect()
     * @dataProvider providerForSetSelectAndSetExpandAndSetOrderBy
     */
    public function test_setSelectWithPassedValidParameter($value, $expects): void
    {
        $result = $this->parameters->setSelect($value);
        $this->assertInstanceOf(QueryParameters::class, $result);
        $this->assertEquals($expects, $this->parameters->select);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setExpand()
     * @dataProvider providerForSetSelectAndSetExpandAndSetOrderBy
     */
    public function test_setExpandWithPassedValidParameter($value, $expects): void
    {
        $result = $this->parameters->setExpand($value);
        $this->assertInstanceOf(QueryParameters::class, $result);
        $this->assertEquals($expects, $this->parameters->expand);
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setOrderBy()
     * @dataProvider providerForSetSelectAndSetExpandAndSetOrderBy
     */
    public function test_setOrderByWithPassedValidParameter($value, $expects): void
    {
        $result = $this->parameters->setOrderBy($value);
        $this->assertInstanceOf(QueryParameters::class, $result);
        $this->assertEquals($expects, $this->parameters->orderBy);
    }

    public function providerForSetSelectAndSetExpandAndSetOrderBy(): array
    {
        $array = ['Ref_key', 'СуммаДокумента'];
        $imploded = implode(',', ['Ref_key', 'СуммаДокумента']);
        return [
            [$array, $imploded],
            [$imploded, $imploded],
        ];
    }

    /**
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setSelect
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setExpand
     * @covers \Alexsmirnovdotcom\Odata\QueryParameters::setOrderBy
     */
    public function test_setSelectAndSetExpandAndSetOrderByWithPassedInvalidParameterMustThrowError(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->parameters->setSelect(new \stdClass());
        $this->parameters->setSelect(10);
        $this->parameters->setExpand(new \stdClass());
        $this->parameters->setExpand(false);
        $this->parameters->setOrderBy(new \stdClass());
        $this->parameters->setOrderBy(false);
    }


}