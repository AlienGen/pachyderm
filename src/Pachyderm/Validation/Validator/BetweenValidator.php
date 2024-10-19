<?php

namespace Pachyderm\Validation\Validator;

use Pachyderm\Validation\ValidatorInterface;

class BetweenValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        return $value >= $options[0] && $value <= $options[1];
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The value must be between ' . $options[0] . ' and ' . $options[1] . '.';
    }
}
