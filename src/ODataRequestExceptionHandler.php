<?php

namespace Alexsmirnovdotcom\Odata;

use Alexsmirnovdotcom\Odata\Exceptions\ODataServiceException;
use Alexsmirnovdotcom\Odata\Exceptions\Request\AuthException;
use Alexsmirnovdotcom\Odata\Exceptions\Request\NotFoundException;
use Alexsmirnovdotcom\Odata\Exceptions\Request\RequestException;
use Alexsmirnovdotcom\Odata\Exceptions\Service\ConnectionException;
use Alexsmirnovdotcom\Odata\Exceptions\Service\ServiceException;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;

class ODataRequestExceptionHandler implements Interfaces\RequestExceptionHandlerInterface
{
    /**
     * @param Exception $e
     * @param OData $client
     * @return void
     * @throws AuthException
     * @throws ConnectionException
     * @throws NotFoundException
     * @throws ODataServiceException
     * @throws RequestException
     * @throws ServiceException
     */
    public function handle(Exception $e, OData $client): void
    {
        if ($e instanceof GuzzleRequestException) {
            $response = $e->getResponse();
            $code = $e->getCode();
            switch ($code) {
                case 401:
                    $message = $response ? $response->getReasonPhrase() : 'Unauthorized.';
                    throw new AuthException($message, $code, $response, $e);
                case 404:
                    $message = $response ? $response->getReasonPhrase() : 'Not Found.';
                    throw new NotFoundException($message, $code, $response, $e);
                default:
                    $message = $response ? $response->getReasonPhrase() : null;
                    throw new RequestException($message, $code, $response, $e);
            }
        } elseif ($e instanceof ConnectException) {
            throw new ConnectionException($client->debug(), $e->getMessage(), $e->getCode(), $e);
        } elseif ($e instanceof GuzzleException) {
            throw new ODataServiceException($e->getMessage(), $e->getCode(), $e);
        } else {
            throw $e;
        }
    }
}
