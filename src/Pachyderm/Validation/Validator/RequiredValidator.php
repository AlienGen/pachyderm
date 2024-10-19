<?php

namespace Pachyderm\Validation\Validator;

use Pachyderm\Validation\ValidatorInterface;

class RequiredValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        return !empty($value);
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The field is required';
    }
}
