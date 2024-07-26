<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class ObjectValidator extends InputValidator
{
    protected string $argument = 'object';

    public function options() : array
    {
        return [
            'translate' => [
                'required' => false,
                'type' => 'array',
                'keys' => ['from', 'to']
            ],
            'escape' => [
                'required' => false,
                'type' => 'bool'
            ]
        ];
    }

    public function validate(mixed &$value, ?array $options = []) : bool
    {
        $value = $this->parseString($value);
        
        if ( @ $options['translate'] ) $value = strtr($value, $options['translate']['from'], $options['translate']['to']);
        if ( @ $options['escape'] ) $value = \Clicalmani\Fundation\Support\Facades\Str::escape($value);

        $value = json_decode( $value );
        
        if ( JSON_ERROR_NONE !== json_last_error() ) return false;

        return true;
    }
}
