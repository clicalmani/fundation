<?php
namespace Clicalmani\Fundation\Messenger\Receiver\Storage;

use Clicalmani\XPower\XDT;

abstract class AbstractStorage extends \ArrayObject
{
    private ?XDT $xdt = null;

    public function __construct()
    {
        $this->xdt = xdt();
        $this->xdt->setDirectory(__DIR__);
        $this->xdt->connect('storage', true, true);
    }

    public function offsetGet(mixed $key): mixed
    {
        if (isset($this[$key])) {
            return $this->xdt->select('message[id=' . $key . ']');
        }

        return null;
    }
    
    public function offsetSet(mixed $index, mixed $message) : void
    {
        $messageId = $this->nextID();
        $serialized = serialize($message);
        $this->xdt->getDocumentRootElement()->append("<message id='$messageId'>$serialized</message>");
        $this->xdt->close();
        parent::offsetSet($index, $message);
    }

    public function offsetUnset(mixed $key): void
    {
        if (isset($this[$key])) {
            $this->xdt->getDocumentRootElement()->children()->pos($key)->remove();
            $this->xdt->close();
            parent::offsetUnset($key);
        }
    }

    public function offsetExists(mixed $key): bool
    {
        return $this->xdt->select('message[id=' . $key . ']')->length;
    }

    public function first()
    {
        $children = $this->xdt->getDocumentRootElement()->children();
        if ($children->length) return $children->first();

        return null;
    }

    public function last()
    {
        $children = $this->xdt->getDocumentRootElement()->children();
        if ($children->length) return $children->last();

        return null;
    }

    public function count(): int
    {
        return $this->xdt->getDocumentRootElement()->children()->length;
    }

    private function nextID() : int
    {
        if (NULL !== $last = $this->last()) {
            return $last->attr('id') + 1;
        }

        return 0;
    }
}
