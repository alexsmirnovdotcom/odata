<?php

namespace Alexsmirnovdotcom\Odata\Exceptions\Service;

use Alexsmirnovdotcom\Odata\Response;
use Throwable;

class IllegalKeyOffsetException extends ServiceException
{
    protected Response $response;

    public function __construct(Response $response, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
