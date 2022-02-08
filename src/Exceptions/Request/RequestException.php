<?php

namespace Alexsmirnovdotcom\Odata\Exceptions\Request;

use Alexsmirnovdotcom\Odata\Exceptions\ODataServiceException;
use GuzzleHttp\Psr7\Response;
use Throwable;

class RequestException extends ODataServiceException
{
    protected ?Response $response;

    public function __construct(?string $message = "", ?int $code = 0, ?Response $response = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    /**
     * @return Response|null
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
