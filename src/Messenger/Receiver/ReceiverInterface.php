<?php
namespace Clicalmani\Fundation\Messenger\Receiver;

use Clicalmani\Fundation\Messenger\Envelope\EnvelopeInterface;

interface ReceiverInterface
{
    /**
     * @return iterable<int, \Clicalmani\Fundation\Messenger\Message\MessageInterface>
     */
    public function get() : iterable;

    /**
     * Store new message
     * 
     * @param \Clicalmani\Fundation\Messenger\Envelope\EnvelopeInterface $envelope
     */
    public function store(EnvelopeInterface $envelope) : void;
}
