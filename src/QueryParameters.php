<?php

namespace Alexsmirnovdotcom\Odata;

use Alexsmirnovdotcom\Odata\Constants\Inlinecount;
use Alexsmirnovdotcom\Odata\Constants\Metadata;
use Alexsmirnovdotcom\Odata\Exceptions\Service\InvalidParameterException;
use Alexsmirnovdotcom\Odata\Interfaces\QueryParametersInterface;
use ReflectionClass;

class QueryParameters implements QueryParametersInterface
{
    protected string $resource = '';
    protected string $guid = '';
    protected string $filter = '';
    protected string $select = '';
    protected string $expand = '';
    protected array $data = [];
    protected ?int $skip = null;
    protected ?int $top = null;
    protected string $orderBy = '';
    protected ?string $inlineCount = null;
    protected bool $isOnlyCount = false;
    protected string $format = '';

    public function getQueryAndBody(): array
    {
        return [
            'query' => $this->getParameters(),
            'body' => json_encode($this->getData(), JSON_THROW_ON_ERROR),
        ];
    }

    public function getParameters(): array
    {
        $params = [
            '$filter' => $this->filter,
            '$select' => $this->select,
            '$expand' => $this->expand,
            '$skip' => $this->skip,
            '$top' => $this->top,
            '$orderby' => $this->orderBy,
            '$inlinecount' => $this->inlineCount,
            '$format' => $this->format,
        ];
        return array_filter($params, static fn ($value) => !empty($value));
    }

    public function getData(): array
    {
        return array_filter($this->data, static function ($val) {
            return empty($val) || is_string($val) || is_numeric($val) || is_bool($val);
        });
    }

    public function setData(array $data): QueryParametersInterface
    {
        $this->data = $data;
        return $this;
    }

    public function setMetadataLevel(string $level): QueryParametersInterface
    {
        Metadata::check($level);
        $this->format = $level;
        return $this;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function setResource(string $resource): QueryParametersInterface
    {
        $this->resource = trim($resource);
        return $this;
    }

    public function resetExclude(...$props): QueryParametersInterface
    {
        $defaultProps = $this->getPropertiesDefaults();
        $forReset = array_diff(array_keys($defaultProps), $props);
        foreach ($forReset as $prop) {
            $this->$prop = $defaultProps[$prop];
        }
        return $this;
    }

    protected function getPropertiesDefaults(): array
    {
        return (new ReflectionClass($this))->getDefaultProperties();
    }

    public function resetOnly(...$props): QueryParametersInterface
    {
        $defaultProps = $this->getPropertiesDefaults();
        $forReset = array_intersect($props, array_keys($defaultProps));
        foreach ($forReset as $prop) {
            $this->$prop = $defaultProps[$prop];
        }
        return $this;
    }

    public function setTrueIsOnlyCount(): QueryParametersInterface
    {
        $this->isOnlyCount = true;
        return $this;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    /**
     * @throws InvalidParameterException
     */
    public function setGuid(string $guid = ''): QueryParametersInterface
    {
        preg_match('/[a-fA-F\d]{8}(?:-[a-fA-F\d]{4}){3}-[a-fA-F\d]{12}/m', $guid, $match);

        if (!isset($match[0])) {
            throw new InvalidParameterException("Value: '{$guid}' is not in GUID format or empty. ");
        }

        $this->guid = "(guid'$match[0]')";
        return $this;
    }

    public function clearGuid(): QueryParametersInterface
    {
        $this->guid = '';
        return $this;
    }

    public function setInlinecount(string $inlinecount): QueryParametersInterface
    {
        Inlinecount::check($inlinecount);
        $this->inlineCount = $inlinecount;
        return $this;
    }

    public function isOnlyCount(): bool
    {
        return $this->isOnlyCount;
    }

    public function setTop($value): QueryParametersInterface
    {
        $this->top = max($value, 0);
        return $this;
    }

    public function setSelect($select): QueryParametersInterface
    {
        $this->checkIsArrayOrStringAndThrowExceptionOnFalse($select);
        $this->select = is_string($select) ? trim($select) : implode(',', $select);
        return $this;
    }

    protected function checkIsArrayOrStringAndThrowExceptionOnFalse($value): void
    {
        if (!is_string($value) && !is_array($value)) {
            throw new InvalidParameterException('Ожидается строка или массив, получено: ' . gettype($value));
        }
    }

    public function setSkip($value): QueryParametersInterface
    {
        $this->skip = max($value, 0);
        return $this;
    }

    public function setFilter(string $filter): QueryParametersInterface
    {
        $this->filter = trim($filter);
        return $this;
    }

    public function setOrderBy($values): QueryParametersInterface
    {
        $this->checkIsArrayOrStringAndThrowExceptionOnFalse($values);
        $this->orderBy = is_string($values) ? trim($values) : implode(',', $values);
        return $this;
    }

    public function setExpand($expand): QueryParametersInterface
    {
        $this->checkIsArrayOrStringAndThrowExceptionOnFalse($expand);
        $this->expand = is_string($expand) ? trim($expand) : implode(',', $expand);
        return $this;
    }
}
