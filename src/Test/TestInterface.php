<?php 
namespace Clicalmani\Fundation\Test;

/**
 * TestInterface Interface
 * 
 * @package clicalmani/fundation
 * @author @clicalmani
 */
interface TestInterface
{
    /**
     * Count the number of time to iterate the test case.
     * 
     * @param int $num Counter
     * @return static
     */
    public function count(int $num) : static;

    /**
     * Manipulate the test state. It allows to provide a new data set for each instance.
     * 
     * @param callable $callback A callable function that receive default attributes and return the 
     * attributes to override by.
     * @return static
     */
    public function state(?callable $callback) : static;

    /**
     * Run the test case
     * 
     * @return void
     */
    public static function test() : void;
}
