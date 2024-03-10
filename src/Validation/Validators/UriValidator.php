<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class UriValidator extends InputValidator
{
    protected string $argument = 'uri';

    public function validate(mixed &$value, ?array $options = []) : bool
    {
        return !! filter_var($value, FILTER_VALIDATE_URL);
    }
}
