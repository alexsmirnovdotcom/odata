<?php

namespace Alexsmirnovdotcom\Odata\Interfaces;

use Alexsmirnovdotcom\Odata\Exceptions\Service\InvalidParameterException;

interface QueryParametersInterface
{
    public function setMetadataLevel(string $level): QueryParametersInterface;

    public function resetExclude(...$props): QueryParametersInterface;

    public function resetOnly(...$props): QueryParametersInterface;

    public function setTrueIsOnlyCount(): QueryParametersInterface;

    public function getGuid(): string;

    /**
     * При переданном значении которое не подходит под формат GUID выбрасывается исключение
     * InvalidParameterException.
     *
     * Если переданная строка содержит больше символов, но ее часть подходит под формат GUID
     * именно она будет установлена в качестве значения.
     *
     * @throws InvalidParameterException
     * @param string $guid
     * @return QueryParametersInterface
     */
    public function setGuid(string $guid = ''): QueryParametersInterface;

    public function clearGuid(): QueryParametersInterface;

    public function setInlinecount(string $inlinecount): QueryParametersInterface;

    public function isOnlyCount(): bool;

    public function getParameters(): array;

    public function getData(): array;

    public function setData(array $data): QueryParametersInterface;

    public function setTop($value): QueryParametersInterface;

    public function setSelect($select): QueryParametersInterface;

    public function setSkip(int $value): QueryParametersInterface;

    public function setFilter(string $filter): QueryParametersInterface;

    public function setOrderBy($values): QueryParametersInterface;

    public function setExpand($expand): QueryParametersInterface;

    public function getQueryAndBody(): array;
}
