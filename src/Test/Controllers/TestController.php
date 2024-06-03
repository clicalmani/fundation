<?php 
namespace Clicalmani\Fundation\Test\Controllers;

use Clicalmani\Database\Factory\Sequence;
use Clicalmani\Fundation\Auth\EncryptionServiceProvider;
use Clicalmani\Fundation\Http\Requests\Request;
use Clicalmani\Fundation\Test\TestInterface;

abstract class TestController implements TestInterface
{
    /**
     * Request controller
     * 
     * @var \Clicalmani\Fundation\Http\Requests\RequestController
     */
    protected $controller;

    /**
     * Holds the current action to be tested.
     * 
     * @var string
     */
    private $action;

    /**
     * Holds the request parameters to be used for the test.
     * 
     * @var array
     */
    private $parameters = [];

    /**
     * Holds the fake test user if test should
     * be driven in a user environment.
     * 
     * @var int
     */
    private $user;

    /**
     * Holds the number of time to repeat the test.
     * 
     * @var int
     */
    private $counter = 1;
    
    /**
     * Holds parameters hash.
     * 
     * @var string|\Clicalmani\Database\Factory\Sequence
     */
    private $hash;

    /**
     * Holds the request headers.
     * 
     * @var array|\Clicalmani\Database\Factory\Sequence
     */
    private $headers;

    /**
     * Merges parameters
     * 
     * @param ?array $parameters
     * @return array 
     */
    private function merge(?array $parameters = []) : array
    {
        return array_merge($this->parameters, $parameters);
    }

    /**
     * Override parameters
     * 
     * @param array $parameters Only specified parameters will be overriden
     * @return array New seed
     */
    private function override(?array $parameters = [])
    {
        $this->parameters = $this->merge($parameters);
        $parameters = $this->{$this->action}();
        
        foreach ($this->parameters as $key => $value) {
            $parameters[$key] = $value;
        }

        return $parameters;
    }

    /**
     * Set request hash
     * 
     * @return void
     */
    private function setHash() : void
    {
        $hash_parameter = EncryptionServiceProvider::hashParameter();
        if ($this->hash instanceof Sequence) {
            $this->override( [$hash_parameter => create_parameters_hash( call($this->hash) )]);
        } else $this->override( [$hash_parameter => $this->hash] );
    }

    /**
     * Set request headers
     * 
     * @return void
     */
    private function setHeaders() : void
    {
        if ($this->headers instanceof Sequence) $this->override( call($this->headers) );
    }

    /**
     * Create a new test
     * 
     * @param string $action Action method
     * @return static
     */
    public function new(string $action) : static
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Manipulate the factory state
     * 
     * @param callable $callback A callable function that receive default attributes and return the 
     * attributes to override by.
     * @return static
     */
    public function state(?callable $callback) : static
    {
        $this->override( $callback( $this->{$this->action}() ) );
        return $this;
    }

    /**
     * Provides a user for the test.
     * 
     * @param int|\Clicalmani\Database\Factory\Sequence $param
     * @return static
     */
    public function user(int|Sequence $param) : static
    {
        if ( is_int($param) ) $this->user = (int) $param;
        elseif ( $param instanceof Sequence ) $this->user = $param();
        return $this;
    }

    /**
     * Repeat the test n times.
     * 
     * @param int $n Counter
     * @return static
     */
    public function count($n = 1) : static
    {
        $this->counter = $n;
        return $this;
    }

    /**
     * Make the test
     * 
     * @return void
     */
    public function make($attributes = []) : void
    {
        foreach (range(1, $this->counter) as $num) {

            $request = new Request;

            /**
             * Request hash
             */
            if ($this->hash) $this->setHash();

            /**
             * Headers
             */
            if ($this->headers) $this->setHeaders();

            $parameters = $this->override($attributes);

            /**
             * Parameter sequence
             */
            foreach ($parameters as $key => $param) {
                if ($param instanceof Sequence) $parameters[$key] = call( $param );
            }

            $request->make( $parameters );
            
            /**
             * User sequence
             */
            if ($this->user) {
                if ($this->user instanceof Sequence) {
                    $request->test_user_id = call( $this->user );
                } else $request->test_user_id = $this->user;
            }
            
            print_r( $this->controller::invokeControllerMethod($this->controller, $this->action) );
            if ($num < $this->counter) echo "\n";
        }
    }

    /**
     * Provides a hash parameter for the request.
     * 
     * @param array|\Clicalmani\Database\Factory\Sequence $parameters
     * @return static
     */
    public function hash(Sequence|array $parameters) : static
    {
        if ( is_array($parameters) ) $this->hash = with( new Request )->createParametersHash($parameters);
        elseif ( $parameters instanceof Sequence ) $this->hash = $parameters;
        return $this;
    }

    /**
     * Set a request header.
     * 
     * @param string $name
     * @param string $value
     * @return static
     */
    public function header(string $name, string $value) : static
    {
        $this->override( [$name => $value] );
        return $this;
    }

    /**
     * Set request headers
     * 
     * @param array|\Clicalmani\Database\Factory\Sequence $headers
     * @return static
     */
    public function headers(Sequence|array $headers) : static
    {
        if ( is_array($headers) ) $this->override( $headers );
        elseif ( $headers instanceof Sequence ) $this->headers = $headers;
        return $this;
    }
}
