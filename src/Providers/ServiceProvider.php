<?php
namespace Clicalmani\Fundation\Providers;

use Clicalmani\Fundation\Container\SPL_Loader;

/**
 * ServiceProvider class
 * 
 * @package Clicalmani\Fundation/flesco 
 * @author @Clicalmani\Fundation
 */
abstract class ServiceProvider
{
    /**
     * Service container
     * 
     * @var \Clicalmani\Fundation\Container\SPL_Loader
     */
    protected $container;

    /**
     * Event listeners
     * 
     * @var array
     */
    protected $listen = [];
    
    public function __construct()
    {
        $this->container = new SPL_Loader;
    }

    /**
     * App config
     * 
     * @var array
     */
    protected static $app;

    /**
     * Service kernel
     * 
     * @var array
     */
    protected static $kernel;

    /**
     * Http middlewares
     * 
     * @var array
     */
    protected static $http_kernel;

    /**
     * (non-PHPDoc)
     * @override
     */
    public abstract function boot() : void;

    /**
     * (non-PHPDoc)
     * @override
     */
    public function register() : void { /** TODO: Override */}

    /**
     * Bootstrap providers
     * 
     * @param array $kernel
     * @param array $http_kernel
     * @return void
     */
    public static function init(array $app, array $kernel, array $http_kernel) : void
    {
        static::$app         = $app;
        static::$kernel      = $kernel;
        static::$http_kernel = $http_kernel;

        static::provideServices();
    }

    /**
     * Make custom helper functions available
     * 
     * @return void
     */
    public static function helpers() : void
    {
        foreach (self::customHelpers() as $helper) {
            with( new $helper )->boot();
        }
    }

    /**
     * Retrieve provided custom helpers
     * 
     * @return array
     */
    public static function customHelpers() : array
    {
        if ( $custom_helpers = static::$app['helpers'] ) return $custom_helpers;

        return [];
    }

    /**
     * Get a provided middleware
     * 
     * @param string $gateway
     * @param string $name Middleware name
     * @return mixed
     */
    public static function getProvidedMiddleware(string $gateway, $name) : mixed
    {
        return @ static::$http_kernel[$gateway][$name];
    }

    private static function getServiceProviders()
    {
        return @ static::$app['providers'] ?? [];
    }

    private static function provideService(string $service_class)
    {
        if ( class_exists( $service_class ) ) {
            $service = new $service_class;
            
            if ( method_exists($service, 'register') ) $service->register();
            if ( method_exists($service, 'boot') ) $service->boot();
        }
    }

    private static function provideServices()
    {
        foreach (self::getServiceProviders() as $service)
            self::provideService($service);
    }
}
