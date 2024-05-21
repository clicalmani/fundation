<?php 
namespace Clicalmani\Fundation\Support;

/**
 * Mock class
 * 
 * Mock allows us to call a non-static method as static one and take advantage of the class object. 
 * Method name start with an underscore (_) to differentiate it from the calling method name.
 * 
 * @package Clicalmani\Fundation/flesco 
 * @author @Clicalmani\Fundation
 */
class Mock 
{
    /**
     * PHP magic __callStatic
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args) : mixed
    {
        $class = get_called_class();

        if ( method_exists($class, "_$method") ) {
            return with( new $class )->{"_$method"}( ...$args );
        } else throw new \Exception("Method $method does not exists on " . $class);
    }
}
