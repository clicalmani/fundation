<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class NumbersValidator extends InputValidator
{
    protected string $argument = 'number[]';

    public function validate(mixed &$value, ?array $options = []) : bool
    {
        $value = explode(',', $this->parseString( $value ));

        foreach ($value as $entry) {
            if ( ! is_numeric($entry) ) return false;
        }

        return true;
    }
}
