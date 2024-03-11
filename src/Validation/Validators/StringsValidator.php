<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class StringsValidator extends InputValidator
{
    protected string $argument = 'string[]';

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
        $value = explode(',', $this->parseString( $value ));

        foreach ($value as $index => $entry) {

            if ( $options['length'] && strlen($entry) !== $options['length'] ) return false;
        
            if ( $options['min'] && strlen($entry) < $options['min'] ) return false;

            if ( $options['max'] && strlen($entry) > $options['max'] ) {
                $value[$index] = substr($entry, 0, $options['max']);
            }

            if ( is_numeric($entry) ) return false;
        }

        return true;
    }
}
