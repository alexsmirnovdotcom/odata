<?php

namespace Alexsmirnovdotcom\Odata\Exceptions\Service;

use Throwable;

class ConnectionException extends ServiceException
{
    protected array $debug;

    public function __construct(array $debug = [], $message = "", $code = 0, Throwable $previous = null)
    {
        $this->debug = $debug;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getDebug(): array
    {
        return $this->debug;
    }
}
