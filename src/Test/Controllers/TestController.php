<?php 
namespace Clicalmani\Fundation\Test\Controllers;

use Clicalmani\Database\Factory\Sequence;
use Clicalmani\Fundation\Http\Requests\Request;
use Clicalmani\Fundation\Test\TestCase;

abstract class TestController extends TestCase
{
    /**
     * User model class
     * 
     * @var \Clicalmani\Database\Factory\Models\Model Object
     */
    protected $controller;

    private $action, 
            $override = [], 
            $user, 
            $counter = 1,
            $hash,
            $headers;

    /**
     * Merges parameters
     * 
     * @param array $parameters [Optional]
     * @return array 
     */
    private function merge(?array $parameters = []) : array
    {
        return array_merge($this->override, $parameters);
    }

    /**
     * Override parameters
     * 
     * @param array $parameters Only specified parameters will be overriden
     * @return array New seed
     */
    private function override(?array $parameters = [])
    {
        $this->override = $this->merge($parameters);
        $parameters = $this->{$this->action}();
        
        foreach ($this->override as $attribute => $value) {
            $parameters[$attribute] = $value;
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
        if ($this->hash instanceof Sequence) {
            $this->override( ['hash' => with( new Request )->createParametersHash( call($this->hash) )]);
        } else $this->override( ['hash' => $this->hash] );
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
     * Manipulate factory states
     * 
     * @param callable $callback A callable function that receive default attributes and return the 
     * attributes to override.
     * @return static
     */
    public function state(?callable $callback) : static
    {
        $this->override( $callback( $this->{$this->action}() ) );
        return $this;
    }

    /**
     * Request user
     * 
     * @param callable $param A callable function to return the request user id or an integer value.
     * @return static
     */
    public function user(Sequence|int $param) : static
    {
        if ( is_int($param) ) $this->user = (int) $param;
        elseif ( $param instanceof Sequence ) $this->user = $param;
        return $this;
    }

    /**
     * Repeat the seed operation n times.
     * 
     * @param int $num Counter
     * @return $this
     */
    public function count($num = 1) : static
    {
        $this->counter = $num;
        return $this;
    }

    /**
     * Make test
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

            print_r( with( new $this->controller )
                    ->invokeControllerMethod($this->controller, $this->action) );
            if ($num < $this->counter) echo "\n";
        }
    }

    /**
     * Create hash parameter
     * 
     * @param callable $callback
     * @return static
     */
    public function hash(Sequence|array $parameters) : static
    {
        if ( is_array($parameters) ) $this->hash = with( new Request )->createParametersHash($parameters);
        elseif ( $parameters instanceof Sequence ) $this->hash = $parameters;
        return $this;
    }

    /**
     * Set header
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
     * @param Sequence|array $headers
     * @return static
     */
    public function headers(Sequence|array $headers) : static
    {
        if ( is_array($headers) ) $this->override( $headers );
        elseif ( $headers instanceof Sequence ) $this->headers = $headers;
        return $this;
    }
}
