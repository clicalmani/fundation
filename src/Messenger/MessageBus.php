<?php
namespace Clicalmani\Fundation\Messenger;

use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;

class MessageBus implements MessageBusInterface
{
    public function __construct(HandlersLocatorInterface $locator)
    {
        
    }
}
