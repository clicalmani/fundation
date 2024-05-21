<?php
namespace Clicalmani\Fundation\Providers;

use Clicalmani\Fundation\Support\Env;

/**
 * EnvServiceProvider class
 * 
 * @package Clicalmani\Fundation/flesco 
 * @author @Clicalmani\Fundation
 */
class EnvServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Env::enablePutenv();

        /**
         * Load environment variables
         */
        \Dotenv\Dotenv::create(
            Env::getRepository(), 
            dirname( __DIR__, 5)
        )->safeLoad();
    }
}