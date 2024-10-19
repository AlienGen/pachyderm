<?php

namespace Pachyderm\Validation\Validator;

use Pachyderm\Validation\ValidatorInterface;

class TimezoneValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        return in_array($value, \DateTimeZone::listIdentifiers());
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The value must be a valid timezone.';
    }
}
