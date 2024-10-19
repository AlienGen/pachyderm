<?php

namespace Pachyderm\Validation\Validator;

use Pachyderm\Validation\ValidatorInterface;

class EmailValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The field must be a valid email address';
    }
}
