<?php 
namespace Clicalmani\Fundation\Providers;

abstract class RouteTPS 
{
    /**
     * Current route
     * 
     * @var string
     */
    protected $route;

    /**
     * Request object
     * 
     * @var \Clicalmani\Fundation\Http\Requests\Request
     */
    protected $request;

    public function __construct()
    {
        $this->request = new \Clicalmani\Fundation\Http\Requests\Request;
    }

    /**
     * Abort request
     * 
     * @return void
     */
    public function abort() : void
    {
        $this->route = false;
    }

    /**
     * @override
     */
    public function redirect()
    {
        throw new \Exception(sprintf("%s::%s must be overriden. Thrown in %s at line %d", __CLASS__, __METHOD__, static::class, __LINE__));
    }
}
