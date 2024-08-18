<?php
namespace Clicalmani\Fundation\Providers;

/**
 * SessionStorageServiceProvider Class
 * 
 * @package clicalmani/fundation 
 * @author @clicalmani
 */
abstract class SessionStorageServiceProvider extends ServiceProvider
{
    /**
     * Session driver
     * 
     * This option controls the default session "driver" that will be used on
     * requests. By default, we will use the lightweight native driver but
     * you may specify any of the other wonderful drivers provided here.
     *
     * Supported: "file", "cookie", "database", "apc", "memcached", "redis", "dynamodb", "array"
     * 
     * @var string
     */
    protected static $driver = \Clicalmani\Fundation\Http\Session\FileSessionHandler::class;

    /**
     * Here you may specify the number of seconds that you wish the session
     * to be allowed to remain idle before it expires.
     * 
     * @var int
     */
    protected static $lifetime = 10;

    /**
     * Here you may specify the maximum number of minutes that you wish the session
     * should be idle.
     * 
     * @var int
     */
    protected static $max_lifetime = 20;

    /**
     * If you want session to immediately expire on the browser closing, set that option.
     * 
     * @var bool
     */
    protected static $expire_on_close = false;

    /**
     * This option allows you to easily specify that all of your session data
     * should be encrypted before it is stored. All encryption will be run
     * automatically by and you can use the Session like normal.
     * 
     * @var bool
     */
    protected static $encrypt = false;

    /**
     * When using the native session driver, we need a location where session
     * files may be stored. A default has been set for you but a different
     * location may be specified. This is only needed for file sessions.
     * 
     * @var string
     */
    protected static $files = './';

    /**
     * When using the "database" or "redis" session drivers, you may specify a
     * connection that should be used to manage these sessions. This should
     * correspond to a connection in your database configuration options.
     * 
     * @var string
     */
    protected static $connection = 'mysql';

    /**
     * When using the "database" session driver, you may specify the table we
     * should use to manage the sessions. Of course, a sensible default is
     * provided for you; however, you are free to change this as needed.
     * 
     * @var string
     */
    protected static $table = 'sessions';

    /**
     * While using one of the framework's cache driven session backends you may
     * list a cache store that should be used for these sessions. This value
     * must match with one of the application's configured cache "stores".
     *
     * Affects: "apc", "dynamodb", "memcached", "redis"
     * 
     * @var array
     */
    protected static $store = [];

    /**
     * Some session drivers must manually sweep their storage location to get
     * rid of old sessions from storage. Here are the chances that it will
     * happen on a given request. By default, the odds are 2 out of 100.
     * 
     * @var array
     */
    protected static $lotery = [1, 100];

    /**
     * Here you may change the cookie settings used to identify a session
     * instance by ID.
     * 
     * @var array
     */
    protected static $cookie = [
                                'name' => '_SESSION_COOKIE',
                                'path' => '/',
                                'domain' => '',
                                'secure' => false,
                                'http_only' => false,
                                'samesite' => true
                            ];

    private $session_dir;

    public function __construct()
    {
        $this->session_dir = dirname( __DIR__, 5) . '/storage/framework/sessions';
        
        if (!is_dir($this->session_dir)) {
            mkdir($this->session_dir, 0777, true);
        }

        $config = [
            'session.save_handler' => 'files',
            'session.save_path' => realpath($this->session_dir),
            'session.use_cookies' => 1,
            'session.name' => static::$cookie['name'],
            'session.auto_start' => 0,
            'session.cookie_lifetime' => static::$max_lifetime,
            'session.cookie_path' => static::$cookie['path'],
            'session.cookie_domain' => static::$cookie['domain'],
            'session.cookie_samesite' => (int)static::$cookie['samesite'],
            'session.cookie_secure' => (int)static::$cookie['secure'],
            'session.cookie_httponly' => (int)static::$cookie['http_only'],
            'session.serialize_handler' => 'php',
            'session.gc_probability' => static::$lotery[0],
            'session.gc_divisor    ' => static::$lotery[1],
            'session.gc_maxlifetime' => static::$max_lifetime,
            'session.cache_limiter' => 'nocache',
            'session.use_strict_mode' => 1
        ];

        if (FALSE === inConsoleMode())
            foreach ($config as $k => $v) ini_set($k, $v);
    }

    public function boot(): void
    {
        if (FALSE === inConsoleMode()) 
            // Start a session
            if (session_status() === PHP_SESSION_NONE) {
                
                session_set_save_handler(
                    new static::$driver(static::$encrypt, [
                        'table' => env('DB_TABLE_PREFIX') . static::$table,
                        'driver' => static::$connection
                    ]), 
                    true
                );
                register_shutdown_function('session_write_close');
                session_start();
                
                $_SESSION['_IDLE'] = @$_SESSION['_IDLE'] ?? time();
                $_SESSION['_LAST_ACTIVITY'] = @$_SESSION['_LAST_ACTIVITY'] ?? time();
                setcookie(
                    static::$cookie['name'],
                    session_id(),
                    time() + static::$max_lifetime,
                    static::$cookie['path'],
                    static::$cookie['domain'],
                    static::$cookie['secure'],
                    static::$cookie['http_only']
                );
                
                if (isset($_SESSION['_LAST_ACTIVITY']) && (time() - $_SESSION['_LAST_ACTIVITY'] > static::$max_lifetime)) {
                    // last request was more than $lifetime seconds ago
                    session_unset();     // unset $_SESSION variable for the run-time 
                    session_destroy();   // destroy session data in storage
                }
                
                if (isset($_SESSION['_IDLE']) && (time() - $_SESSION['_IDLE'] > static::$lifetime)) {
                    // session started more than $max_lifetime seconds ago
                    session_regenerate_id(true);  // change session ID for the current session and invalidate old session ID
                    $_SESSION['_IDLE'] = time();  // update creation time
                }
            }
    }

    /**
     * Returns session table
     * 
     * @return string
     */
    public static function getTable() : string
    {
        return static::$table;
    }
}
