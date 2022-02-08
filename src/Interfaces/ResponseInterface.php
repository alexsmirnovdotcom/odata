<?php

namespace Alexsmirnovdotcom\Odata\Interfaces;

use Alexsmirnovdotcom\Odata\Exceptions\Service\IllegalKeyOffsetException;
use JsonException;
use JsonSerializable;

interface ResponseInterface extends JsonSerializable
{
    public function __construct(bool $error, int $code = 0, string $message = '', string $body = '');

    /**
     * Вернулся ли запрос с ошибкой.
     *
     * @return bool
     */
    public function isFailed(): bool;

    /**
     * Код ответа полученный от сервера (Status Code).
     *
     * @return int
     */
    public function getCode(): int;

    /**
     * Сообщение полученное от сервера (Reason Phrase).
     *
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * Возвращает тело запроса из json строки в виде массива.
     * В $key можно передать callback, тогда в него будет передана строка body
     * и вернется результат выполнения. (Например если вы ходите трансформировать не json, а XML).
     * При указанном параметре $key, возвращает только значение по этому ключу
     * или выбрасывает KeyNotExistsInResponseException.
     *
     * @param string|callable|null $key
     * @return mixed|null
     * @throws IllegalKeyOffsetException
     * @throws JsonException
     */
    public function getBody($key = null);
}
