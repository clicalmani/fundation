<?php
namespace Clicalmani\Fundation\Messenger\Receiver\Storage;

class StorageIterator extends \ArrayIterator
{
    private $storage, $index = 0;

    public function key(): string|int|null
    {
        return $this->index;
    }

    public function next(): void
    {
        $this->index++;
    }

    public function valid(): bool
    {
        $this->getStorage();
        return !!$this->storage->get($this->index);
    }

    public function current(): mixed
    {
        return $this->storage->get($this->index);
    }

    private function getStorage()
    {
        if (!$this->storage) $this->storage = new Storage;

        return $this->storage;
    }
}
