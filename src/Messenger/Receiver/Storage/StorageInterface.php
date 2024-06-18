<?php
namespace Clicalmani\Fundation\Messenger\Receiver\Storage;

use Clicalmani\Fundation\Messenger\Message\MessageInterface;

interface StorageInterface
{
    /**
     * Gets a stored message by index
     * 
     * @param int $index
     * @return \Clicalmani\Fundation\Messenger\Message\MessageInterface
     */
    public function get(int $index) : MessageInterface|null;

    /**
     * Store or update a message by index
     * 
     * @param int $index
     * @param Clicalmani\Fundation\Messenger\Message\MessageInterface $message
     * @return void
     */
    public function set(int $index, MessageInterface $message) : void;

    /**
     * Destroy a stored message
     * 
     * @param int $index
     * @return void
     */
    public function unset(int $index) : void;

    /**
     * Check if a message exists at the specified index
     * 
     * @param int $index
     * @return bool
     */
    public function exists(int $index) : bool;

    /**
     * Last message ID.
     * 
     * @return int
     */
    public function lastID(): int;
}
