<?php

namespace Pachyderm\Validation\Validator;

use Pachyderm\Validation\ValidatorInterface;

class ArrayValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        return is_array($value);
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The value must be an array.';
    }
}
