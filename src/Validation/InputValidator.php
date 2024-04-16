<?php
namespace Clicalmani\Fundation\Validation;

use Exception;

class InputValidator implements ValidatorInterface
{
    use InputParser;
    use ParseValidator;

    /**
     * Holds the validator signature.
     * 
     * @var string
     */
    protected string $signature;
    
    /**
     * Validator argument
     * 
     * @var string
     */
    protected string $argument;

    /**
     * Default validation arguments
     * 
     * @var string[]
     */
    private $defaultArguments = ['required', 'nullable'];

    /**
     * 
     */
    private $validated = [];

    public function validate(mixed &$value, ?array $options = [] ) : bool
    {
        throw new \Exception("Validate method must be overriden.");

        return true;
    }

    public function options() : array
    {
        return [
            // Options
        ];
    }

    /**
     * Argument getter
     * 
     * @return string
     */
    public function getArgument() : string
    {
        return $this->argument;
    }

    public function isRequired() : bool
    {
        if ( -1 !== $this->getArguments()->index('required') ) return true;
        return false;
    }

    public function isNullable() : bool
    {
        if ( -1 !== $this->getArguments()->index('nullable') ) return true;
        return false;
    }

    /**
     * Sanitize input
     * 
     * @param array &$inputs
     * @param array $signatures
     * @return void
     */
    public function sanitize(array &$inputs, array $signatures) : void
    {
        foreach ($signatures as $param => $sig) {
            
            if ( in_array($param, $this->validated) ) continue;

            $this->signature = $sig;
            
            if ( $this->isRequired() && ! array_key_exists($param, $inputs) ) throw new \Exception("Parameter $param is required.");

            if ( array_key_exists($param, $inputs) ) {
                
                if ( $this->isNullable() && $inputs[$param] == '' ) {
                    $inputs[$param] = null;
                    continue;
                }

                /**
                 * Validator argument
                 * 
                 * @var string
                 */ 
                $argument = $this->getArguments()->filter(fn(string $argument) => ! in_array($argument, $this->defaultArguments))->first();
                
                /**
                 * Provided options
                 * 
                 * @var array
                 */
                $options = $this->getArgumentOptions($argument);
                
                /**
                 * Validator
                 * 
                 * @var static
                 */
                $validatorClass = ( new \App\Providers\InputValidationProvider )->getValidator($argument);
                
                if ( $validatorClass ) {

                    $validator = new $validatorClass;

                    /**
                     * Validator options
                     * 
                     * @var array
                     */
                    $voptions = $validator->options();
                    
                    // Check validator options validity.
                    foreach ($voptions as $option => $data) {

                        if ( !array_key_exists($option, $options) ) continue;
                        
                        // A required option not provided
                        if ( @ $data['required'] && ! array_key_exists($option, $options) ) throw new \Exception( sprintf("Option %s is required for %s validator.", $option, $argument) );
                        
                        // Execute option function
                        if ( $fn = @ $data['function'] ) $options[$option] = $fn($options[$option]);

                        // Set option type
                        if ( @ $data['type'] ) settype($options[$option], $data['type']);

                        // Set array key (for array options)
                        if ( @ $data['keys'] ) {
                            
                            $keys = $data['keys'];
                            $tmp = [];

                            foreach ($keys as $i => $key) {
                                $tmp[$key] = @ $options[$option][$i];
                            }

                            $options[$option] = $tmp;
                        }
                        
                        // Option validator
                        if ( !!@$options[$option] && $fn = @ $data['validator'] AND false === $fn($options[$option]) ) throw new \Exception( sprintf("%s is not a valid option %s value for %s validator.", $options[$option], $option, $argument) ); 
                    }
                    
                    foreach ($options as $option => $value) {
                        if ( $option && ! array_key_exists($option, $voptions) ) throw new \Exception( sprintf("%s is not a valid %s validator option.", $option, $argument) );
                    }

                    if ( false === $validator->validate($inputs[$param], $options) ) throw new \Exception( sprintf("Parameter %s is not valid.", $param) );

                } else throw new \Exception("$argument is not a valid validator argument.");
            }
        }
    }

    /**
     * Keep track of parameters which validator passed.
     * 
     * @param string $param
     * @return void
     */
    public function passed(string $param) : void
    {
        $this->validated[] = $param;
    }

    public function __get($name)
    {
        if ($name === 'signature') return $this->signature;
    }

    public function __set($name, $value)
    {
        if ($name === 'signature') $this->signature = $value;
    }
}
