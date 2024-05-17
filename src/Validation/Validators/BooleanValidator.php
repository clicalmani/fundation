<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class BooleanValidator extends InputValidator
{
    protected string $argument = 'boolean';
    
    public function validate(mixed &$value, ?array $options = []) : bool
    {
        if ($value) $value = true;
        if (NULL === $value || (is_numeric($value) && $this->parseInt($value) === 0)) $value = false;
        
        return true;
    }
}
