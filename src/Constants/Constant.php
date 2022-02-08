<?php

namespace Alexsmirnovdotcom\Odata\Constants;

use Alexsmirnovdotcom\Odata\Exceptions\Service\InvalidParameterException;

class Constant
{
    /**
     * @param string $value
     * @return void
     * @throws InvalidParameterException
     */
    public static function check(string $value): void
    {
        $reflection = new \ReflectionClass(static::class);
        $constants = $reflection->getConstants();
        if (!array_key_exists($value, $constants) && !in_array($value, $constants, true)) {
            throw new InvalidParameterException(
                'Неверное значение. Значение должно быть из списка: '
                . implode(', ', array_values($constants))
                . ' или одной из констант: '
                . implode(', ', array_keys($constants))
            );
        }
    }
}
