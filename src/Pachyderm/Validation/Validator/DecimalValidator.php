<?php

namespace Pachyderm\Validation\Validator;

use Pachyderm\Validation\ValidatorInterface;

class DecimalValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        return is_numeric($value) && strpos($value, '.') !== false;
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The value must be a decimal number.';
    }
}
