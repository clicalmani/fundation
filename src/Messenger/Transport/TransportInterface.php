<?php
namespace Clicalmani\Fundation\Messenger\Transport;

use Clicalmani\Fundation\Messenger\Message\MessageInterface;

interface TransportInterface
{
    /**
     * Check transport
     * 
     * @return bool
     */
    public function check() : bool;

    /**
     * Send message
     * 
     * @param \Clicalmani\Fundation\Messenger\Message\MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message) : void;
}
