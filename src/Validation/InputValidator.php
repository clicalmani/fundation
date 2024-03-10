<?php
namespace Clicalmani\Fundation\Validation;

abstract class InputValidator
{
    use InputParser;
    
    /**
     * Validator argument
     * 
     * @var string
     */
    protected $argument;

    /**
     * Validate input
     * 
     * @param string &$value Value to validate
     * @param ?array $options Value options
     * @return bool
     */
    abstract public function validate(mixed &$value, ?array $options = [] ) : bool;

    /**
     * Validator options
     * 
     * @return array
     */
    public function options() : array
    {
        return [
            // Options
        ];
    }
}
