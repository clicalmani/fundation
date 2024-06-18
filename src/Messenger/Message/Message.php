<?php
namespace Clicalmani\Fundation\Messenger\Message;

class Message implements MessageInterface
{
    /**
     * Destination address
     * 
     * @var string
     */
    private string $to;

    /**
     * Sender address
     * 
     * @var string
     */
    private string $from;

    /**
     * Message body
     * 
     * @var string
     */
    private string $body;
    
    public function __construct($message = null)
    {
        [$to, $from, $body] = [ ...$message ];
        
        $this->to = $to;
        $this->from = $from;
        $this->body = $body;
    }

    public function getToAddress() : string
    {
        return $this->to;
    }

    public function getFromAddress() : string
    {
        return $this->from;
    }

    public function getBody() : string
    {
        return $this->body;
    }

    public function __serialize(): array
    {
        return [
            'to'   => $this->to,
            'from' => $this->from,
            'body' => $this->body
        ];
    }
}
