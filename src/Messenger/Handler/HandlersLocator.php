<?php
namespace Clicalmani\Fundation\Messenger\Handler;

class HandlersLocator implements HandlersLocatorInterface
{
    /**
     * Message handlers
     * 
     * @var array
     */
    private $handlers;

    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    public function getHandlers(): iterable
    {
        $handlers = [];
        
        foreach ($this->handlers as $sender => $handler) {
            $handler = (array)$handler;
            foreach ($handler as $h) $handlers[] = new HandlerDescriptor($sender, $h);
        }

        return $this->handlers = $handlers;
    }
}
