<?php
namespace Clicalmani\Fundation\Http\Client;

/**
 * Class Curl
 * 
 * Curl wrapper used by HttpClient to make curl requests.
 * @package Clicalmani\Fundation
 * @author @Clicalmani\Fundation
 */
class Curl
{
    /**
     * Curl handle
     * 
     * @var \CurlHandle|false
     */
    protected \CurlHandle|false $curl = FALSE;

    public function __construct($curl = FALSE)
    {
        if (FALSE === $curl) {
            $curl = curl_init();
        }

        $this->curl = $curl;
    }

    public function setOpt(int $option, mixed $value) : static
    {
        curl_setopt($this->curl, $option, $value);
        return $this;
    }

    public function close() : static
    {
        curl_close($this->curl);
        return $this;
    }

    public function exec() : string|bool
    {
        return curl_exec($this->curl);
    }

    public function errNo() : int
    {
        return curl_errno($this->curl);
    }

    public function getInfo(?int $option = NULL) : mixed
    {
        return curl_getinfo($this->curl, $option);
    }

    public function error() : string
    {
        return curl_error($this->curl);
    }
}