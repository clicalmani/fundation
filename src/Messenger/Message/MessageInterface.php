<?php
namespace Clicalmani\Fundation\Messenger\Message;

interface MessageInterface
{
    /**
     * Returns the destination address
     * 
     * @return string
     */
    public function getToAddress() : string;

    /**
     * Returns the sender address
     * 
     * @return string
     */
    public function getFromAddress() : string;

    /**
     * Return message body
     * 
     * @return string
     */
    public function getBody() : string;
}
