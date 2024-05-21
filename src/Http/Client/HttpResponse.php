<?php
namespace Clicalmani\Fundation\Http\Client;

/**
 * Class HttpResponse
 * @package PayPalHttp
 *
 * Object that holds your response details
 */
class HttpResponse
{
    /**
     * @var integer
     */
    public $statusCode;

    /**
     * @var object | string
     */
    public $result;

    /**
     * @var array
     */
    public $headers;

    public function __construct($statusCode, $body, $headers)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->result = $body;
    }
}