<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class EnumValidator extends InputValidator
{
    protected string $argument = 'enum';

    public function options() : array
    {
        return [
            'list' => [
                'required' => true,
                'type' => 'array',
                'function' => fn(string $value) => explode(',', $value)
            ]
        ];
    }

    public function validate(mixed &$value, ?array $options = []) : bool
    {
        $value = $this->parseString($value);
        $list = $this->parseArray($options['list']);
        
        return !! in_array($value, $list);
    }
}
