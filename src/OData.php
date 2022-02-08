<?php

namespace Alexsmirnovdotcom\Odata;

use Alexsmirnovdotcom\Odata\Constants\Inlinecount;
use Alexsmirnovdotcom\Odata\Constants\Metadata;
use Alexsmirnovdotcom\Odata\Constants\Methods;
use Alexsmirnovdotcom\Odata\Exceptions\Service\InvalidParameterException;
use Alexsmirnovdotcom\Odata\Interfaces\ConfigInterface;
use Alexsmirnovdotcom\Odata\Interfaces\QueryParametersInterface;
use Alexsmirnovdotcom\Odata\Interfaces\RequestExceptionHandlerInterface;
use Alexsmirnovdotcom\Odata\Interfaces\ResponseInterface;
use Alexsmirnovdotcom\Odata\Interfaces\ServiceInterface;
use Exception;
use GuzzleHttp\Client;

/**
 * Class OData
 */
class OData implements ServiceInterface
{
    protected Client $client;

    protected Config $config;

    protected QueryParameters $parameters;

    protected RequestExceptionHandlerInterface $exceptionHandler;

    public function __construct(
        ?ConfigInterface                  $config = null,
        ?Client                           $client = null,
        ?QueryParametersInterface         $queryParameters = null,
        ?RequestExceptionHandlerInterface $exceptionHandler = null
    )
    {
        $this->config = $config ?? new Config();
        $this->client = $client ?? new Client();
        $this->parameters = $queryParameters ?? new QueryParameters();
        $this->exceptionHandler = $exceptionHandler ?? new ODataRequestExceptionHandler();
    }

    public function authAs(array $auth): OData
    {
        $this->config->setAuth($auth);
        return $this;
    }

    public function resource(string $resource): OData
    {
        return $this->from($resource);
    }

    public function from(string $resource): OData
    {
        $this->parameters->setResource($resource);
        return $this;
    }

    public function getOnlyCount(): Response
    {
        $this->parameters->setTrueIsOnlyCount();
        return $this->get();
    }

    public function get(string $guid = ''): Response
    {
        if (!empty($guid)) {
            $this->parameters->resetOnly('filter', 'data');
            $this->parameters->setGuid($guid);
        }
        return $this->request(Methods::GET);
    }

    protected function request(string $method): ResponseInterface
    {
        try {
            Methods::check($method);
            $uri = $this->compileURI();
            $params = array_merge($this->config->getConfigParameters(), $this->parameters->getQueryAndBody());
            $result = $this->client->request($method, $uri, $params);
            $code = $result->getStatusCode();
            $reasonPhrase = $result->getReasonPhrase();
            $body = $result->getBody();
            $response = $this->createResponse(false, $code, $reasonPhrase, $body);
        } catch (Exception $e) {
            $this->exceptionHandler->handle($e, $this);
        }

        return $response;
    }

    protected function compileURI(): string
    {
        $host = $this->config->getHost();
        if (empty($host)) {
            throw new InvalidParameterException('Host URI is empty.');
        }
        $resource = $this->parameters->getResource();
        $guid = $this->parameters->getGuid();
        $countParameter = empty($guid) && $this->parameters->isOnlyCount() ? '$count' : '';
        return "{$host}/odata/standard.odata/{$resource}{$guid}/{$countParameter}";
    }

    protected function createResponse(bool $error, int $code, string $reasonPhrase, ?string $body): ResponseInterface
    {
        return new Response($error, $code, $reasonPhrase, $body);
    }

    public function debug(): array
    {
        return [
            'URI' => $this->compileURI(),
            'Config' => $this->config->getConfigParameters(),
            'QueryParameters' => $this->parameters->getQueryAndBody(),
        ];
    }

    public function noMetadata(): OData
    {
        $this->parameters->setMetadataLevel(Metadata::NO);
        return $this;
    }

    public function setInlineCountAllPages(): OData
    {
        $this->parameters->setInlinecount(Inlinecount::ALLPAGES);
        return $this;
    }

    public function setInlineCountNone(): OData
    {
        $this->parameters->setInlinecount(Inlinecount::NONE);
        return $this;
    }

    public function limit(int $value): OData
    {
        $this->top($value);
        return $this;
    }

    public function top(int $value): OData
    {
        $this->parameters->setTop($value);
        return $this;
    }

    public function skip(int $value): OData
    {
        $this->parameters->setSkip($value);
        return $this;
    }

    public function select($select): OData
    {
        $this->parameters->setSelect($select);
        return $this;
    }

    public function create(array $data = []): Response
    {
        $this->parameters->resetExclude('resource');
        $this->parameters->clearGuid();
        $this->parameters->setData($data);
        return $this->request(Methods::POST);
    }

    public function update(string $guid, array $data = []): Response
    {
        $this->parameters->resetExclude('resource');
        $this->parameters->setGuid($guid);
        $this->parameters->setData($data);
        return $this->request(Methods::PATCH);
    }

    public function markDeleted(string $guid, string $deletionMarkKey = 'DeletionMark'): Response
    {
        $deletionMarkKey = empty($deletionMarkKey) ? 'DeletionMark' : $deletionMarkKey;
        $this->parameters->resetExclude('resource');
        $this->parameters->setGuid($guid);
        $this->parameters->setData([$deletionMarkKey => true]);
        return $this->request(Methods::PATCH);
    }

    public function forceDelete(string $guid): Response
    {
        $this->parameters->resetExclude('resource');
        $this->parameters->setGuid($guid);
        return $this->request(Methods::DELETE);
    }

    public function filter(string $filter): OData
    {
        $this->parameters->setFilter($filter);
        return $this;
    }

    public function orderBy($orders): OData
    {
        $this->parameters->setOrderBy($orders);
        return $this;
    }

    public function expand($expand): OData
    {
        $this->parameters->setExpand($expand);
        return $this;
    }

    public function setHost(string $host): OData
    {
        $this->config->setHost($host);
        return $this;
    }

    public function setHeader(string $header, string $value): ServiceInterface
    {
        $this->config->setHeader($header, $value);
        return $this;
    }

    public function clearHeader(string $header): ServiceInterface
    {
        $this->config->clearHeader($header);
        return $this;
    }

    public function setAdditionalClientParams(array $params): ServiceInterface
    {
        $this->config->setClientParameters($params);
        return $this;
    }

    public function setRequestExceptionHandler(RequestExceptionHandlerInterface $handler): ServiceInterface
    {
        $this->exceptionHandler = $handler;
        return $this;
    }
}
