<?php
namespace Clicalmani\Fundation\Http\Client\Sample;

use Clicalmani\Fundation\Http\Client\HttpClient;
use Clicalmani\Fundation\Http\Client\HttpRequest;
use Clicalmani\Fundation\Http\Client\Injector;

/**
 * Class AuthorizationInjector
 * 
 * @package Clicalmani\Fundation
 * @author @Clicalmani\Fundation
 */
class AuthorizationInjector implements Injector
{
    /**
     * Http client
     * 
     * @var HttpClient
     */
    private $client;

    /**
     * Environment
     * 
     * @var PDMSEnvironment
     */
    private $environment;

    /**
     * Access token
     * 
     * @var AccessTokent
     */
    public $accessToken;

    /**
     * Constructor
     * 
     * @param HttpClient $client
     * @param PDMSEnvironment $environment
     */
    public function __construct(HttpClient $client, PDMSEnvironment $environment)
    {
        $this->client = $client;
        $this->environment = $environment;
    }

    /**
     * Inject
     * 
     * @param HttpRequest $request
     * @return void
     */
    public function inject(HttpRequest $request) : void
    {
        if (!$this->hasAuthHeader($request) && !$this->isAuthRequest($request))
        {
            if (is_null($this->accessToken) || $this->accessToken->isExpired())
            {
                $this->accessToken = $this->fetchAccessToken();
            }

            $request->headers['Authorization'] = 'Bearer ' . $this->accessToken->token;
        }
    }

    /**
     * Fetch access token
     * 
     * @return AccessToken
     */
    private function fetchAccessToken() : AccessToken
    {
        $accessTokenResponse = $this->client->execute(new AccessTokenRequest($this->environment));
        $accessToken = $accessTokenResponse->result;
        return new AccessToken($accessToken->access_token, $accessToken->token_type, $accessToken->expires_in);
    }

    /**
     * Check if is authorization request
     * 
     * @param HttpRequest $request
     * @return bool
     */
    private function isAuthRequest(HttpRequest $request) : bool
    {
        return $request instanceof AccessTokenRequest;
    }

    /**
     * Has auth header
     * 
     * @param HttpRequest $request
     * @return bool
     */
    private function hasAuthHeader(HttpRequest $request) : bool
    {
        return @ $request->headers['Authorization'];
    }
}
