<?php

namespace Pachyderm\Validation\Validator;

use DateTime;
use Pachyderm\Validation\ValidatorInterface;

class DateValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        return DateTime::createFromFormat('Y-m-d', $value) !== false;
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The value must be a date.';
    }
}
