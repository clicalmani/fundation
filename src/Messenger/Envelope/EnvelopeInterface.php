<?php
namespace Clicalmani\Fundation\Messenger\Envelope;

use Clicalmani\Fundation\Messenger\Message\Message;

interface EnvelopeInterface
{
    public function getMessage() : Message;

    public function getStamps();
}
