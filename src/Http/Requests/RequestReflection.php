<?php
namespace Clicalmani\Fundation\Http\Requests;

/**
 * Class RequestReflection
 * 
 * @package Clicalmani\Fundation
 * @author @Clicalmani\Fundation
 */
class RequestReflection 
{
    private $reflect;

    public function __construct(string $controller, string $method)
    {
        $this->reflect = new \ReflectionMethod($controller, $method);
    }

    public function getParameters()
    {
        return  $this->reflect->getParameters(); 
    }

    public function getParamsTypes()
    {
        $ret = [];

        foreach ($this->reflect->getParameters() as $param) 
            $ret[$param->getName()] = $param->getType()?->getName();
        return $ret;
    }

    public function getParamTypeAt()
    {
        $types = $this->getParamsTypes();
        return array_shift( $types );
    }

    public function getParamsNames()
    {
        $names = [];

        foreach ($this->reflect->getParameters() as $param) {
            $names[] = $param->getName();
        }

        return $names;
    }
}
