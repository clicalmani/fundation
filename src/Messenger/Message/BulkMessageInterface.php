<?php
namespace Clicalmani\Fundation\Messenger\Message;

interface BulkMessageInterface
{
    public function getToAddresses() : iterable;

    public function getFromAddress() : string;

    public function getBody() : string;
}
