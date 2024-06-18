<?php
namespace Clicalmani\Fundation\Messenger\Transport;

use App\Providers\MessageServiceProvider;
use Clicalmani\Fundation\Messenger\Envelope\Envelope;
use Clicalmani\Fundation\Messenger\Message\MessageInterface;

abstract class Transport implements TransportInterface
{
    public function __construct(private string $dns)
    {
        //
    }

    public function send(MessageInterface $message) : void
    {
        $handlers = (new MessageServiceProvider)->getHandlers();

        if (NULL === $handler = @$handlers[$message::class]) throw new \Exception("No handler");

        (new $handler)->handle(new Envelope($message));
    }
}
