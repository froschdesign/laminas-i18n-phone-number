<?php

declare(strict_types=1);

namespace Laminas\I18n\PhoneNumber\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\I18n\PhoneNumber\CountryCode;
use Laminas\I18n\PhoneNumber\Exception\ExceptionInterface;
use Laminas\I18n\PhoneNumber\PhoneNumberValue;

use function is_scalar;

abstract class AbstractFilter implements FilterInterface
{
    private CountryCode $countryCode;

    final public function __construct(CountryCode $countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /** @param mixed $value */
    protected function mixedToPhoneNumber($value): ?PhoneNumberValue
    {
        if ($value instanceof PhoneNumberValue) {
            return $value;
        }

        if (! is_scalar($value)) {
            return null;
        }

        $input = (string) $value;
        if ($input === '') {
            return null;
        }

        try {
            return PhoneNumberValue::fromString($input, $this->countryCode->toString());
        } catch (ExceptionInterface $error) {
            return null;
        }
    }
}
