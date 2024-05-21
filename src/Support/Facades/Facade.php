<?php
namespace Clicalmani\Fundation\Support\Facades;

class Facade 
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
        try {
            $class = self::getClass();
            
            if ( method_exists($class, "$method") ) {
                return with( new $class )->{"$method"}( ...$args );
            }

            throw new \Exception(
                sprintf("Method %s does not exists on class %s. Called at line %d in %s", $method, get_called_class(), __LINE__, __CLASS__)
            );

        } catch (\ArgumentCountError $e) {
            throw new \ArgumentCountError($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * PHP function get_called_class() wrapper
     * 
     * @return string
     */
    private static function getClass() : string
    {
        $class = get_called_class();
        
        if ( $class === \Clicalmani\Fundation\Routing\Route::class )
            return \Clicalmani\Routing\Routing::class;

        $class = "Clicalmani\Fundation\Logic\Internal\\" . substr($class, strrpos($class, "\\") + 1);

        if ( class_exists($class) ) return $class;

        throw new \Exception(sprintf("Facade class %s does not exists.", $class));
    }
}
