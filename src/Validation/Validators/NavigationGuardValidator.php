<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator as Validator;
use Clicalmani\Routing\Cache;

class NavigationGuardValidator extends Validator
{
    /**
     * Validator argument
     * 
     * @var string
     */
    protected string $argument = "nguard";

    /**
     * Validator options
     * 
     * @return array
     */
    public function options() : array
    {
        return [
            'uid' => [
                'required' => true
            ]
        ];
    }

    /**
     * Validate input
     * 
     * @param mixed &$value Input value
     * @param ?array $options Validator options
     * @return bool
     */
    public function validate(mixed &$value, ?array $options = [] ) : bool
    {
        if ( $guard = Cache::getGuard($options['uid']) AND is_callable($guard['callback']) ) return $guard['callback']($value);

        return false;
    }
}
