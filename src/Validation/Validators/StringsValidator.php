<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class StringsValidator extends InputValidator
{
    protected string $argument = 'string[]';

    public function validate(mixed &$value, ?array $options = []) : bool
    {
        $value = explode(',', $this->parseString( $value ));

        foreach ($value as $entry) {
            if ( ! is_string($entry) ) return false;
        }

        return true;
    }
}
