<?php

namespace Pachyderm\Validation\Validator;

use Pachyderm\Validation\ValidatorInterface;

class MinValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool  
    {
        return $value >= $options[0];
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The value must be greater than or equal to ' . $options[0] . '.';
    }
}
