<?php
namespace Clicalmani\Fundation\Http\Requests;

/**
 * HttpRequest class
 * 
 * @package Clicalmani\Fundation/flesco 
 * @author @Clicalmani\Fundation
 */
abstract class HttpRequest 
{
    /**
     * (non-PHPDoc)
     * @override
     */
    abstract public function render() : never;
}
