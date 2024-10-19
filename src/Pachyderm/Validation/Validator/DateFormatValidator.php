<?php

namespace Pachyderm\Validation\Validator;

use DateTime;
use Pachyderm\Validation\ValidatorInterface;

class DateFormatValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        $format = $options['format'] ?? 'Y-m-d';
        $date = DateTime::createFromFormat($format, $value);
        return $date && $date->format($format) === $value;
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The value must be a date in the format ' . $options['format'] ?? 'Y-m-d';
    }
}
