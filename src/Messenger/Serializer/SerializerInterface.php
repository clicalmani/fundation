<?php
namespace Clicalmani\Fundation\Messenger\Serializer;

interface SerializerInterface
{
    /**
     * Verify whether it is a serialized data
     * 
     * @param string $data
     * @param ?bool $strict
     * @return bool
     */
    public function isSerialized(string $data, ?bool $strict = false) : bool;
}
