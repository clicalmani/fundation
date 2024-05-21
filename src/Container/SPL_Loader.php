<?php
namespace Clicalmani\Fundation\Container;

/**
 * Class SPL_Loader
 * 
 * @package Clicalmani\Fundation\Container
 * @author @clicalmani
 */
class SPL_Loader extends Manager
{
    /**
     * Lazy load
     * 
     * @param string $classname
     * @return void
     */
    public function lazyLoad(string $classname) : void
    {
        $this->cache($classname);
    }

    /**
     * Load
     * 
     * @param string $classname
     * @return never
     */
    public function load(string $classname) : never
    {
        $this->current_class = $classname;
        $this->require( $this->find() );
    }

    /**
     * Inject into service container
     * 
     * @param string|callable $class_or_file
     * @return void
     */
    public function inject(string|callable $class_or_file) : void
    {
        if (is_callable($class_or_file)) include_once $class_or_file();
        else $this->lazyLoad($class_or_file);
    }
}
