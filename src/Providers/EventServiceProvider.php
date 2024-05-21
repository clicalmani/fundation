<?php
namespace Clicalmani\Fundation\Providers;

/**
 * EventServiceProvider class
 * 
 * @package Clicalmani\Fundation/flesco 
 * @author @Clicalmani\Fundation
 */
abstract class EventServiceProvider extends ServiceProvider
{
    private static $events = [];

    /**
     * Create a custom event
     * 
     * @param string $event Event name
     * @return void
     */
    protected function createEvent(string $event) : void
    {
        static::$events[$event] = [];
    }

    /**
     * Create multiple custom events
     * 
     * @param string[] $events Events names
     * @return void
     */
    protected function createEvents(array $events) : void
    {
        foreach($events as $event) static::$events[$event] = [];
    }

    /**
     * Add event listener
     * 
     * @param string $event Event name
     * @param callable|string $listener Event listener
     * @return void
     */
    protected function addListener(string $event, callable|string $listener) : void
    {
        static::$events[$event][] = $listener;
    }

    /**
     * Add event listeners
     * 
     * @param string $event Event name
     * @param array $listeners Event listeners
     * @return void
     */
    protected function addListeners(string $event, array $listeners = []) : void
    {
        foreach ($listeners as $listener) $this->addListener($event, $listener);
    }

    /**
     * Return custom events
     * 
     * @return array<string, mixed>
     */
    public static function getEvents() : array
    {
        return static::$events;
    }
}
