<?php
namespace Clicalmani\Fundation\Providers;

use Clicalmani\Fundation\Messenger\Handler\HandlersLocatorInterface;

abstract class MessageServiceProvider extends ServiceProvider
{
    /**
     * Registered handlers
     * 
     * @var \iterable
     */
    protected static $handlers = [];

    public function setHandlers(HandlersLocatorInterface $locator) : void
    {
        static::$handlers = $locator->getHandlers();
    }

    public function getHandlers(): iterable
    {
        return static::$handlers;
    }
}
