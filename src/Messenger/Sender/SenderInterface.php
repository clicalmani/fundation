<?php
namespace Clicalmani\Fundation\Messenger\Sender;

use Clicalmani\Fundation\Messenger\Envelope\EnvelopeInterface;

interface SenderInterface
{
    public function dispatch(EnvelopeInterface $envelope) : void;
}
