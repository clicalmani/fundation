<?php
namespace Clicalmani\Fundation\Messenger\Receiver\Storage;

use Clicalmani\Fundation\Messenger\Message\MessageInterface;

class Storage extends AbstractStorage implements StorageInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->setIteratorClass(StorageIterator::class);
    }

    public function get(int $index) : MessageInterface|null
    {
        if (NULL !== $node = $this[$index]) {
            return unserialize($node->val());
        }

        return null;
    }

    public function set(int $index, MessageInterface $message) : void
    {
        $this[$index] = $message;
    }

    public function unset(int $index) : void
    {
        unset($this[$index]);
    }

    public function exists(int $index) : bool
    {
        return isset($this[$index]);
    }

    public function lastID(): int
    {
        return $this->last()?->attr('id');
    }
}
