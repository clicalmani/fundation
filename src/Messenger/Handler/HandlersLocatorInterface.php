<?php
namespace Clicalmani\Fundation\Messenger\Handler;

use Clicalmani\Fundation\Messenger\Envelope;

interface HandlersLocatorInterface
{
    /**
     * Returns the registered handlers.
     *
     * @return iterable<int, HandlerDescriptor>
     */
    public function getHandlers(): iterable;
}
