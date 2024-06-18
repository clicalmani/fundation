<?php
namespace Clicalmani\Fundation\Messenger\Handler;

final class HandlerDescriptor
{
    public function __construct(private string $sender, private string $handler) 
    {}

    public function getHandler(): callable
    {
        return $this->handler;
    }

    public function getSender(): string
    {
        return $this->sender;
    }
}
