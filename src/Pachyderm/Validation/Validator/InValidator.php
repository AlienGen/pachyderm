<?php

namespace Pachyderm\Validation\Validator;

use Pachyderm\Validation\ValidatorInterface;

class InValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        return in_array($value, $options);
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The value must be one of the following: ' . implode(', ', $options) . '.';
    }
}
