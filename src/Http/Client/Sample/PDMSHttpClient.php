<?php
namespace Clicalmani\Fundation\Http\Client\Sample;

use Clicalmani\Fundation\Http\Client\Core\UserAgent;
use Clicalmani\Fundation\Http\Client\HttpClient;

/**
 * Class PDMSHttpClient
 * 
 * @package Clicalmani\Fundation
 * @author @Clicalmani\Fundation
 */
class PDMSHttpClient extends HttpClient
{
    /**
     * Auth injector
     * 
     * @var AuthorizationInjector
     */
    public $authInjector;

    /**
     * Constructor
     * 
     * @param PDMSEnvironment $environment
     */
    public function __construct(PDMSEnvironment $environment)
    {
        parent::__construct($environment);
        $this->authInjector = new AuthorizationInjector($this, $environment);
        $this->addInjector($this->authInjector);
    }

    /**
     * Get user agent
     * 
     * @return string
     */
    public function userAgent() : string
    {
        return UserAgent::getValue();
    }
}

