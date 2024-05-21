<?php
namespace Clicalmani\Fundation\Http\Middlewares;

use Clicalmani\Fundation\Http\Requests\Request;
use Clicalmani\Fundation\Http\Response\Response;
use Clicalmani\Fundation\Container\SPL_Loader;
use Clicalmani\Fundation\Auth\AuthServiceProvider;

/**
 * Class JWTAuth
 * 
 * @package Clicalmani\Fundation
 * @author @Clicalmani\Fundation
 */
abstract class JWTAuth extends AuthServiceProvider
{
    /**
     * Service container
     * 
     * @var \Clicalmani\Fundation\Container\SPL_Loader
     */
    protected $container;

    public function __construct()
    {
        $this->container = new SPL_Loader;
        parent::__construct();
    }

    /**
     * Handler
     * 
     * @param \Clicalmani\Fundation\Http\Requests\Request $request Request object
     * @param \Clicalmani\Fundation\Http\Response\Response $response Response object
     * @param callable $next Next middleware function
     * @return int|false
     */
    protected abstract function handle(Request $request, Response $response, callable $next) : int|false;

    /**
     * Bootstrap
     * 
     * @return void
     */
    public function boot() : void
    {
        throw new \Exception(sprintf("%s::%s must been override; in %s at line %d", static::class, __METHOD__, __CLASS__, __LINE__));
    }
}
