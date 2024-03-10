<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class BooleanValidator extends InputValidator
{
    protected string $argument = 'boolean';

    public function validate(mixed &$value, ?array $options = []) : bool
    {
        return !! filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
