<?php

namespace Pachyderm\Validation\Validator;

use Pachyderm\Validation\ValidatorInterface;

class RegexValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        return preg_match($options[0], $value);
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The value does not match the required pattern.';
    }
}
