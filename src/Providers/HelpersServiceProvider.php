<?php
namespace Clicalmani\Fundation\Providers;

/**
 * HelpersServiceProvider class
 * 
 * @package Clicalmani\Fundation/flesco 
 * @author @Clicalmani\Fundation
 */
class HelpersServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /**
         * |---------------------------------------------------------------------------
         * |              ***** TONKA built-in helper functions *****
         * |---------------------------------------------------------------------------
         * 
         * Built-in helper functions
         * 
         * 
         */

        \Clicalmani\Fundation\Support\Helper::include();
    }
}