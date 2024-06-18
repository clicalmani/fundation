<?php
namespace Clicalmani\Fundation\Messenger\Transport;

use App\Providers\MessageServiceProvider;
use Clicalmani\Fundation\Messenger\Envelope\EnvelopeInterface;
use Clicalmani\Fundation\Messenger\Message\MessageInterface;

class SMTPTransport extends Transport
{
    public function __construct(private string $dns)
    {
        //
    }

    public function check(): bool
    {
        if (!preg_match('/^(smtp:\/\/)([^:]+):([^:]+)@([^@]+):([^@]+)$/', $this->dns, $matches)) return false;

        $user = $matches[2];
        $pass = $matches[3];
        $host = $matches[4];
        $port = $matches[5];
        
        $success = false;

        if (NULL !== $f = fsockopen($host, $port)) {
            $str = fread($f, 1024);
            echo "Read $str<br>";
            if (strlen($str) > 0 && strpos($str, '220') === 0) $success = true;
        }

        if (PHP_OS === 'WINNT') fwrite($f, "QUIT\r\n");

        return $success;
    }
}
