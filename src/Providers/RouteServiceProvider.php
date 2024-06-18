<?php
namespace Clicalmani\Fundation\Providers;

use Clicalmani\Fundation\Http\Requests\Request;
use Clicalmani\Fundation\Routing\Route;
use Clicalmani\Routing\Cache;
use Clicalmani\Routing\Record;

/**
 * RouteServiceProvider class
 * 
 * @package Clicalmani\Fundation/flesco 
 * @author @Clicalmani\Fundation
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * API prefix
     * 
     * @var string
     */
    protected $api_prefix = 'api';

    /**
     * Default api handler
     * 
     * @var string
     */
    protected $api_handler = 'routes/api.php';

    /**
     * Default web handler
     * 
     * @var string
     */
    protected $web_handler = 'routes/web.php';

    /**
     * Request response
     * 
     * @var mixed
     */
    private static $response_data;

    /**
     * CORS settings
     * 
     * @var array
     */
    private static $cors_settings;
    
    /**
     * Initialize route service
     * 
     * @param callable $callback
     */
    public function routes(callable $callback)
    {
        Record::start('api');
        
        if ( Route::isApi() ) {
            $this->setHeaders();
        } else $this->storeCSRFToken();
        
        $callback();

        Record::clear();
    }

    /**
     * Get api prefix
     * 
     * @return string
     */
    public function getApiPrefix()
    {
        return $this->api_prefix;
    }

    /**
     * Set response headers
     * 
     * @return void
     */
    public function setHeaders()
    {
        if ( isset($_SERVER['HTTP_ORIGIN']) ) {
            header("Access-Control-Allow-Origin: " . static::$cors_settings['allowed_origin']);
            header('Access-Control-Allow-Credentials: ' . static::$cors_settings['allow_credentials']);
            header('Access-Control-Max-Age: ' . static::$cors_settings['max_age']);
        }
    
        /**
         * |-------------------------------------------------------------------
         * |                ***** Preflight Routes *****
         * |-------------------------------------------------------------------
         * 
         * API Request is composed of preflight request and request
         * Prefilght request is meant to check wether the CORS protocol is understood
         */
        if (@ $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: " . join(',', static::$cors_settings['allowed_methods']));         
    
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: " . static::$cors_settings['allowed_headers']);
                
                // Preflight
                response()->status(204, 'PREFLIGHT', '');
                exit;
        }
    }

    /**
     * Store CSRF token
     * 
     * @return void
     */
    public function storeCSRFToken() : void
    {
        // Escape console mode
        if ( FALSE == inConsoleMode() ) {
            // Start a session
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Generate CSRF token and Store it in $_SESSION global variable
            if ( ! isset($_SESSION['csrf-token']) ) {
                $_SESSION['csrf-token'] = with ( new \Clicalmani\Fundation\Auth\CSRF )->getToken(); 
            }
        }
    }

    /**
     * Request response handler
     * 
     * @param callable $callback
     * @return void
     */
    public static function responseHandler(callable $callback) : void
    {
        static::$response_data = $callback( (new Request)->user() );
    }

    /**
     * Get response data
     * 
     * @return mixed
     */
    public static function getResponseData() : mixed
    {
        return static::$response_data;
    }

    /**
     * Get provided third party route services
     * 
     * @param string $service_type
     * @return array
     */
    public static function getProvidedTPS(int $service_level = 0) : array 
    {
        return static::$kernel['tps'][$service_level];
    }

    /**
     * Fire third party services
     * 
     * @param mixed $response Request response
     * @return void
     */
    public static function fireTPS(mixed &$route_response, int $service_level = 0) : void
    {
        foreach (self::getProvidedTPS($service_level) as $tps) {
            if ($service_level === 0) {
                $tps = new $tps($route_response);
            } else {
                $tps = new $tps($route_response, static::$response_data);
            }

            $tps->redirect();
        }
    }

    public function boot(): void
    {
        static::$cors_settings = require_once config_path('/cors.php');
        
        Cache::setRoutes(
            [
                'get'     => [], 
                'post'    => [],
                'options' => [],
                'delete'  => [],
                'put'     => [],
                'patch'   => []
            ]
        );
    }
}
