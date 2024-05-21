<?php
namespace Clicalmani\Fundation\Providers;

/**
 * LogServiceProvider class
 * 
 * @package Clicalmani\Fundation/flesco 
 * @author @Clicalmani\Fundation
 */
class LogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /**
         * Error log
         */
        \Clicalmani\Fundation\Support\Facades\Log::init( root_path() );
    }
}