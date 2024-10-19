<?php

namespace Pachyderm\Validation\Validator;

use Pachyderm\Validation\ValidatorInterface;

class MaxValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        return $value <= $options[0];
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The value must be less than or equal to ' . $options[0] . '.';
    }
}
