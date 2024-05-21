<?php
namespace Clicalmani\Fundation\Providers;

/**
 * ContainerServiceProvider class
 * 
 * @package Clicalmani\Fundation/flesco 
 * @author @Clicalmani\Fundation
 */
class ContainerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /**
         * |----------------------------------------------------------------
         * |            ***** Container AutoLoader *****
         * |----------------------------------------------------------------
         * 
         * Classes defined in the app directory will be automatically injected.
         */
        new \Clicalmani\Fundation\Container\SPL_Loader( root_path() );
    }
}