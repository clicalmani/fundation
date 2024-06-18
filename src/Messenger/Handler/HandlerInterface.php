<?php
namespace Clicalmani\Fundation\Messenger\Handler;

use Clicalmani\Fundation\Messenger\Envelope\EnvelopeInterface;

interface HandlerInterface
{
    public function handle(EnvelopeInterface $envelope) : EnvelopeInterface;
}
