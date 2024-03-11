<?php
namespace Clicalmani\Fundation\Validation;

interface ValidatorInterface
{
    /**
     * Validate input
     * 
     * @param string &$value Value to validate
     * @param ?array $options Value options
     * @return bool
     */
    public function validate(mixed &$value, ?array $options = [] ) : bool;

    /**
     * Validator options
     * 
     * @return array
     */
    public function options() : array;

    /**
     * Input value is required
     * 
     * @return bool
     */
    public function isRequired() : bool;

    /**
     * Input value is nullable
     * 
     * @return bool
     */
    public function isNullable() : bool;
}
