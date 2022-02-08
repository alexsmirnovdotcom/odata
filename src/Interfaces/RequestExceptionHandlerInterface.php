<?php

namespace Alexsmirnovdotcom\Odata\Interfaces;

use Alexsmirnovdotcom\Odata\OData;
use Exception;

interface RequestExceptionHandlerInterface
{
    /**
     * Обработка исключений пойманных при выполнении запроса.
     * В метод будут переданы само исключение и OData клиент.
     *
     * @param Exception $exception
     * @param OData $client
     * @return mixed
     */
    public function handle(Exception $exception, OData $client);
}
