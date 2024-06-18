<?php
namespace Clicalmani\Fundation\Messenger\Receiver;

use Clicalmani\Fundation\Messenger\Envelope\EnvelopeInterface;
use Clicalmani\Fundation\Messenger\Receiver\ReceiverInterface;
use Clicalmani\Fundation\Messenger\Receiver\Storage\Storage;
use Clicalmani\Fundation\Messenger\Receiver\Storage\StorageInterface;

class Receiver implements ReceiverInterface
{
    private StorageInterface $storage;

    public function __construct()
    {
        $this->storage = new Storage;
    }

    public function get(): iterable
    {
        $last_id = $this->storage->lastID();
        $count = 0;

        while ($count <= $last_id) {
            if (isset($this->storage[$count])) yield $this->storage->get($count);
            $count++;
        }
    }

    public function store(EnvelopeInterface $envelope) : void
    {
        $this->storage[] = $envelope->getMessage();
    }
}
