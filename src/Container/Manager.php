<?php
namespace Clicalmani\Fundation\Container;

abstract class Manager
{
    /**
     * Current class to load
     * 
     * @var string
     */
    protected string $current_class;

    /**
     * Custom bindings
     * 
     * @var array
     */
    private $bindings = [
                            'App/' => 'app/',
                            'Database/' => 'database/',
                            'Factories/' => 'factories/',
                            'Seeders/' => 'seeders/',
                        ];

    /**
     * Resolved classes
     * 
     * @var array
     */
    protected static array $resolved_classes = [];

    /**
     * Constructor
     * 
     * @param ?string $root_path
     */
    public function __construct(protected ?string $root_path = null)
    {
        // Registers the autoloader
        spl_autoload_register(function(?string $classname) {
            $this->current_class = $classname;

            $filename = $this->find();
            static::$resolved_classes[$this->current_class] = $filename;
            $this->require( $filename );
        });
    }

    /**
     * Find a class
     * 
     * @return string
     */
    public function find() : string
    {
        if ( $this->isCached() ) return static::$resolved_classes[$this->current_class];
        
        $file_name = str_replace("\\", DIRECTORY_SEPARATOR, $this->getNamespace()) . DIRECTORY_SEPARATOR . $this->getClassName() . '.php';
        $file_name = $this->bind($this->root_path . DIRECTORY_SEPARATOR . $file_name);

        return $file_name;
    }

    /**
     * Returns class namespace
     * 
     * @return string
     */
    public function getNamespace() : string
    {
        if (false !== ($lastNsPos = $this->lastNameSpacePos())) return substr($this->current_class, 0, $lastNsPos);

        return '';
    }

    /**
     * Returns the class file name.
     * 
     * @return string
     */
    public function getClassName() : string
    {
        if (false !== ($lastNsPos = $this->lastNameSpacePos())) return substr($this->current_class, 0, $lastNsPos + 1);

        return '';
    }

    /**
     * Last namespace position
     * 
     * @return int|false
     */
    public function lastNameSpacePos() : int|false
    {
        return strripos($this->current_class, '\\');
    }

    /**
     * Bind dependence
     * 
     * @param string $fullFileName
     * @return string
     */
    public function bind(string $fullFileName) : string
    {
        foreach ($this->bindings as $key => $value) {
            $fullFileName = str_replace($key, $value, $fullFileName);
        }

        return $fullFileName;
    }

    /**
     * Require a file
     * 
     * @param string $file_name
     * @return void
     */
    protected function require(string $file_name) : void
    {
        if ( @ file_exists($file_name) ) require_once $file_name;
    }

    /**
     * Cache a class
     * 
     * @return void
     */
    protected function cache(string $classname) : void
    {
        $this->current_class = $classname;
        static::$resolved_classes[$classname] = $this->find();
    }

    /**
     * Verify if the given class is cached.
     * 
     * @return bool
     */
    private function isCached() : bool
    {
        if ( array_key_exists($this->current_class, static::$resolved_classes) ) return true;

        return false;
    }
}
