<?php
namespace Clicalmani\Fundation\Messenger\Handler;

use Clicalmani\Fundation\Messenger\Envelope\EnvelopeInterface;

class Handler implements HandlerInterface
{
    public function handle(EnvelopeInterface $envelope) : EnvelopeInterface
    {
        return $envelope;
    }
}
