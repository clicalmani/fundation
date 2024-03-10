<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class EmailValidator extends InputValidator
{
    protected $argument = 'email';
    
    public function validate(mixed &$email, ?array $options = []) : bool
    {
        return !! filter_var($this->parseString($email), FILTER_VALIDATE_EMAIL);
    }
}
