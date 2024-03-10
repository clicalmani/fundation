<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class StringValidator extends InputValidator
{
    protected string $argument = 'string';

    public function options() : array
    {
        return [
            'min' => [
                'required' => false,
                'type' => 'int'
            ],
            'max' => [
                'required' => false,
                'type' => 'int'
            ],
            'length' => [
                'required' => false,
                'type' => 'int'
            ]
        ];
    }

    public function validate(mixed &$value, ?array $options = []) : bool
    {
        $value = $this->parseString( $value );
        
        if ( $options['min'] && strlen($value) < $options['min'] ) return false;

        if ( $options['max'] && strlen($value) > $options['max'] ) {
            $value = substr($value, 0, $options['max']);
        }

        if ( $options['length'] && strlen($value) !== $options['length'] ) return false;

        return true;
    }
}
