<?php

namespace Pachyderm\Validation;

use Pachyderm\Validation\Validator\AlphaNumValidator;
use Pachyderm\Validation\Validator\AlphaValidator;
use Pachyderm\Validation\Validator\ArrayValidator;
use Pachyderm\Validation\Validator\BetweenValidator;
use Pachyderm\Validation\Validator\BooleanValidator;
use Pachyderm\Validation\Validator\DateFormatValidator;
use Pachyderm\Validation\Validator\DateValidator;
use Pachyderm\Validation\Validator\DecimalValidator;
use Pachyderm\Validation\Validator\EmailValidator;
use Pachyderm\Validation\Validator\IntegerValidator;
use Pachyderm\Validation\Validator\InValidator;
use Pachyderm\Validation\Validator\MaxValidator;
use Pachyderm\Validation\Validator\MinValidator;
use Pachyderm\Validation\Validator\NotInValidator;
use Pachyderm\Validation\Validator\NumericValidator;
use Pachyderm\Validation\Validator\ObjectValidator;
use Pachyderm\Validation\Validator\RegexValidator;
use Pachyderm\Validation\Validator\RequiredValidator;
use Pachyderm\Validation\Validator\StringValidator;
use Pachyderm\Validation\Validator\TimeValidator;
use Pachyderm\Validation\Validator\TimezoneValidator;
use Exception;

class Validator
{
    private static $validator = null;
    protected $validators = [];

    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    public function validateValue(string $rules, mixed $value): array
    {
        $rules = explode('|', $rules);

        // If the value is null and 'required' is not in the rules, return no errors
        if ($value === null && !in_array('required', $rules)) {
            return [];
        }

        $errors = [];

        foreach ($rules as $rule) {
            $rule = explode(':', $rule);
            $validator = $rule[0];
            $options = isset($rule[1]) ? explode(',', $rule[1]) : [];

            // Check if the validator exists
            if (!isset($this->validators[$validator])) {
                throw new Exception("Validator '{$validator}' not found.");
            }

            if (!$this->validators[$validator]->validate($value, $options)) {
                $errors[] = $this->validators[$validator]->getErrorMessage($options);
            }
        }

        return $errors;
    }

    public static function getInstance(): Validator
    {
        if (self::$validator === null) {
            self::$validator = new Validator([
                'required' => new RequiredValidator(),
                'email' => new EmailValidator(),
                'min' => new MinValidator(),
                'max' => new MaxValidator(),
                'between' => new BetweenValidator(),
                'in' => new InValidator(),
                'not_in' => new NotInValidator(),
                'regex' => new RegexValidator(),
                'string' => new StringValidator(),
                'alpha' => new AlphaValidator(),
                'alpha_num' => new AlphaNumValidator(),
                'numeric' => new NumericValidator(),
                'integer' => new IntegerValidator(),
                'decimal' => new DecimalValidator(),
                'boolean' => new BooleanValidator(),
                'array' => new ArrayValidator(),
                'object' => new ObjectValidator(),
                'date' => new DateValidator(),
                'date_format' => new DateFormatValidator(),
                'time' => new TimeValidator(),
                'timezone' => new TimezoneValidator(),               
            ]);
        }

        return self::$validator;
    }

    public static function validate(string $rules, mixed $value): array
    {
        return self::getInstance()->validateValue($rules, $value);
    }

    public static function addValidator(string $name, ValidatorInterface $validator)
    {
        self::$validators[$name] = $validator;
    }
}
