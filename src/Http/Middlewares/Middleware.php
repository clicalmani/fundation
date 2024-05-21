<?php
namespace Clicalmani\Fundation\Http\Middlewares;

use Clicalmani\Fundation\Container\SPL_Loader;
use Clicalmani\Fundation\Http\Requests\Request;
use Clicalmani\Fundation\Http\Response\Response;
use Clicalmani\Routing\Group;

/**
 * Class Middleware
 * 
 * @package Clicalmani\Fundation
 * @author @Clicalmani\Fundation
 */
abstract class Middleware 
{
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
    protected abstract function boot() : void;

    /**
     * Group routes
     * 
     * @return \Clicalmani\Routing\Group
     */
    public function group() : Group
    {
        return (new Group)->group(fn() => $this->boot());
    }

    /**
     * Inject middleware routes into the service container.
     * 
     * @param string $routes_file Without extension
     * @return void
     */
    protected function include(string $routes_file) : void
    {
        (new SPL_Loader)->inject(fn() => routes_path("$routes_file.php"));
    }
}
