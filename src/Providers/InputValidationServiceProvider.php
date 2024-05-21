<?php
namespace Clicalmani\Fundation\Providers;

class InputValidationServiceProvider extends ServiceProvider
{
    /**
     * Input validators
     * 
     * @var array
     */
    protected static $validators = [];

	/**
	 * Bootstrap input validators
	 * 
	 * @return void
	 */
    public function boot() : void
	{
        $validators = ( new \Clicalmani\Fundation\Validation\Kernel )->validators();
        $custom_validators = @ static::$http_kernel['validators'];

        /**
         * |-------------------------------------------------------
         * |                  ***** Notice *****
         * |-------------------------------------------------------
         * 
         * Custom validators will override builtin validators with same argument names.
         */
        if ( $custom_validators ) $validators = array_merge($validators, $custom_validators);

        if ( $validators )
            foreach ($validators as $validator) {
                static::$validators[( new $validator )->getArgument()] = $validator;
            }
	}

    /**
     * Verify whether a validator argument is valid.
     * 
     * @param string $argument
     * @return bool
     */
    public function seemsValidator(string $argument) : bool
    {
        return !!array_key_exists($argument, $this->getValidators());
    }

    /**
     * Get a validator by its argument.
     * 
     * @param string $argument
     * @return mixed Validator class on success, NULL on failure.
     */
    public function getValidator(string $argument) : mixed
    {
        return @ static::$validators[$argument];
    }

    /**
     * Returns validators
     * 
     * @return array
     */
    public function getValidators() : array
    {
        return static::$validators;
    }
}
