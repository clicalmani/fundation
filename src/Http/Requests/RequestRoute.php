<?php
namespace Clicalmani\Fundation\Http\Requests;

use Clicalmani\Fundation\Routing\Route;

/**
 * Class RequestRoute
 * 
 * @package Clicalmani\Fundation
 * @author @Clicalmani\Fundation
 */
class RequestRoute 
{
    /**
     * Get current route
     * 
     * @return string Current route
     */
    public function current() : string
    {
        return current_route();
    }

    /**
     * Verify if route has been named name.
     * 
     * @param string $name
     * @return bool
     */
    public function named(string $name) : bool
    {
        return !!Route::findByName($name); 
    }
}
