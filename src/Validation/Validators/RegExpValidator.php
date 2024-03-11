<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class RegExpValidator extends InputValidator
{
    protected string $argument = 'regexp';

    public function options() : array
    {
        return [
            'pattern' => [
                'required' => true,
                'type' => 'string'
            ]
        ];
    }

    public function validate(mixed &$value, ?array $options = []) : bool
    {
        $value = $this->parseString($value);
        $pattern = $options['pattern'];

        return !! preg_match("/^$pattern$/", $value);
    }
}
