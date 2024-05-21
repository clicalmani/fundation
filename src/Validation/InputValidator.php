<?php
namespace Clicalmani\Fundation\Validation;

use Clicalmani\Fundation\Providers\InputValidationServiceProvider;
use Clicalmani\Fundation\Support\Facades\Log;

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

    public function __construct(private ?bool $silent = false) {}
    
    public function validate(mixed &$value, ?array $options = [] ) : bool
    {
        $this->log("Must override %s::%s in %s at line %d.", __CLASS__, __METHOD__, $this::class, __LINE__);

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
     * @return bool
     */
    public function sanitize(array &$inputs, array $signatures) : bool
    {
        foreach ($signatures as $param => $sig) {
            
            if ( in_array($param, $this->validated) ) continue;

            $this->signature = $sig;
            
            if ( $this->isRequired() && ! array_key_exists($param, $inputs) ) $this->log("Parameter $param is required.");
            
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
                
                $service = new InputValidationServiceProvider;
                
                if (FALSE === $service->seemsValidator($argument)) $this->log("$argument is not a valid validator argument.");

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
                $validatorClass = ( new InputValidationServiceProvider )->getValidator($argument);
                
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
                    if ( @ $data['required'] && ! array_key_exists($option, $options) ) $this->log(sprintf("Option %s is required for %s validator.", $option, $argument));
                    
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
                    if ( !!@$options[$option] && $fn = @ $data['validator'] AND false === $fn($options[$option]) ) $this->log(sprintf("%s is not a valid option %s value for %s validator.", $options[$option], $option, $argument)); 
                }
                
                foreach ($options as $option => $value) {
                    if ( $option && ! array_key_exists($option, $voptions) ) $this->log(sprintf("%s is not a valid %s validator option.", $option, $argument));
                }

                $success = $validator->validate($inputs[$param], $options);
                
                if ( false === $success ) {
                    if ( FALSE === $this->silent ) $this->log(sprintf("Parameter %s is not valid.", $param));

                    return false;
                }
            }
        }

        return true;
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

    private function log(string $message)
    {
        if ($this->silent) return;
        
        $is_debug_mode = ( 'true' === strtolower(env('APP_DEBUG')) );
        
        if ($is_debug_mode) throw new \Exception($message);

        $backtrace = function(int $index) {
            $trace = @ debug_backtrace()[$index];

            if (!$trace) return false;

            return ['class' => @ $trace['class'], 'line' => @ $trace['line']];
        };

        $index = 0;

        while ($trace = $backtrace($index)) {
            if (in_array($trace['class'], [
                    __CLASS__, 
                    \Clicalmani\Database\Factory\Entity::class, 
                    \Clicalmani\Database\Factory\Models\AbstractModel::class,
                    \Clicalmani\Database\Factory\Models\Model::class
                ])) {
                $index++;
                continue;
            }
            
            Log::error($message, E_ERROR, $trace['class'], $trace['line']);
        }
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
