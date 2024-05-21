<?php 
namespace Clicalmani\Fundation\Test;

abstract class TestCase 
{
    /**
     * Method to call for the test case.
     * 
     * @return static
     */
    public abstract function count(int $num) : static;

    /**
     * Manipulate test states
     * 
     * @param callable $callback A callable function that receive default attributes and return the 
     * attributes to override.
     * @return static
     */
    abstract public function state(?callable $callback) : static;

    /**
     * Run a test case
     * 
     * @return void
     */
    abstract public static function test() : void;
}
