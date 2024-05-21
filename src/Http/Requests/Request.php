<?php
namespace Clicalmani\Fundation\Http\Requests;

use Clicalmani\Fundation\Auth\EncryptionServiceProvider;
use Clicalmani\Fundation\Http\Requests\UploadedFile;
use Clicalmani\Fundation\Http\Requests\RequestRedirect;
use Clicalmani\Fundation\Providers\AuthServiceProvider;
use Clicalmani\Fundation\Routing\Route;

class Request implements RequestInterface, \ArrayAccess, \JsonSerializable 
{
    /**
     * Current request object
     * 
     * @var static
     */
    protected static $current_request;

    /**
     * Validator
     * 
     * @var \Clicalmani\Fundation\Fundation\Validation\InputValidator
     */
    private $validator;

    /**
     * Get or set the current request
     * 
     * @param ?self $request
     * @return mixed
     */
    public static function currentRequest(?self $request = null) : mixed
    {
        if ($request) return static::$current_request = $request;
        return static::$current_request;
    }

    /**
     * Prepare for validation
     * 
     * (non-PHPDoc)
     * @override
     */
    public function signatures() { /** TODO: override */ }

    /**
     * Prepare for validation
     * 
     * (non-PHPDoc)
     * @override
     */
    public function prepareForValidation() {
        // TODO: override
    }

    /**
     * (non-PHPDoc)
     * @override
     */
    public function authorize()
    {
        return true;
    }

    /**
     * (non-PHPDoc)
     * @override
     */
    public function validate(?array $signatures = []) : void
    {
        $this->merge($signatures);
    }

    /**
     * Constructor
     * 
     * @param ?array $signatures Request signatures
     */
    public function __construct(private ?array $signatures = []) 
    {
        $this->validator = new \Clicalmani\Fundation\Validation\InputValidator;

        if (Route::isApi() AND in_array(self::getMethod(), ['patch', 'put'])) {
            
            // Parse input stream
            $params = [];
            new \Clicalmani\Fundation\Http\Requests\ParseInputStream($params);
            
            /**
             * Header application/json
             */
            if ( array_key_exists('parameters', $params) ) $params = $params['parameters'];

            $_REQUEST = array_merge($_REQUEST, $params);
        }
    }

    /**
     * (non-PHPDoc)
     * @override
     */
    public function __get($property)
    {
        $vars = static::all();
        $this->validator->sanitize($vars, $this->signatures ?? []);
        $this->validator->passed($property);
        
        return @ $vars[$property];
    }

    /**
     * (non-PHPDoc)
     * @override 
     */
    public function __set($property, $value)
    {
        $_REQUEST[$property] = $value;
    }

    /**
     * Verify if file has been provided
     * 
     * @param string $name File name
     * @return bool
     */
    public function hasFile(string $name) : bool
    {
        return !!@ $_FILES[$name];
    }

    /**
     * Request file
     * 
     * @param string $name File name
     * @return \Clicalmani\Fundation\Http\Requests\UploadedFile|null
     */
    public function file(string $name) : UploadedFile|null
    {
        if ( $this->hasFile($name) ) {
            return new UploadedFile($name);
        }

        return null;
    }

    /**
	 * (non-PHPdoc)
	 * Override
	 */
    public function offsetExists(mixed $property) : bool {
        return ! is_null($this->$property);
    }

    /**
	 * (non-PHPdoc)
	 * Override
	 */
    public function offsetGet(mixed $property) : mixed {
        return $this->$property;
    }

    /**
     * (non-PHPDoc)
     * Override
     */
    public function offsetSet(mixed $property, mixed $value) : void {
        $this->$property = $value;
    }

    /**
	 * (non-PHPdoc)
	 * Override
	 */
    public function offsetUnset(mixed $property) : void {
        if ($this->$property) {
            unset($_REQUEST[$property]);
        }
    }

    /**
     * Provide a download attachment response header
     * 
     * @param string $filename Download file name
     * @param string $filepath Download file path
     * @return mixed
     */
    public function download($filename, $filepath)  : mixed
    {
        header("Content-Disposition: attachment; filename=$filename");

        if ( file_exists($filepath) ) {
            header('Content-Type: ' . mime_content_type($filepath));
            return readfile($filepath);
        }

        return null;
    }

    /**
     * Merge request signatures
     * 
     * @param ?array $new_signatures New signatures to merge into
     * @return void
     */
    public function merge(?array $new_signatures = []) : void
    {
        $this->signatures = array_merge((array) $this->signatures, $new_signatures);
    }

    /**
     * Gather headers
     * 
     * @return array|false
     */
    public function getHeaders() : array|false
    {
        if ( inConsoleMode() ) return $this->all();
        return apache_request_headers();
    }

    /**
     * Get header value
     * 
     * @param string $header
     * @return mixed
     */
    public function getHeader(string $header) : mixed
    {
        foreach ($this->getHeaders() as $name => $value) {
            if (strtolower($name) == strtolower($header)) return $value;
        }

        return null;
    }

    /**
     * Set response header
     * 
     * @param string $header
     * @param string $value
     * @return void
     */
    public function setHeader($header, $value) : void
    {
        header("$header: $value");
    }

    /**
     * Current request method
     * 
     * @return string
     */
    public function getMethod() : string
    { 
        if ( inConsoleMode() ) return '@console';
        return strtolower( (string) @ $_SERVER['REQUEST_METHOD'] );
    }

    /**
     * Gather request parameters
     * 
     * @return array
     */
    public static function all() : array
    {
        return $_REQUEST;
    }

    /**
     * Check CSRF validity by testing the csrf-token parameter's value.
     * 
     * @return bool
     */
    public function checkCSRFToken() : bool
    {
        return @ $this->{'csrf-token'} === csrf();
    }

    /**
     * Create request parameters hash
     * 
     * @param array $params
     * @return string
     */
    public function createParametersHash($params) : string
    {
        return tap(
            EncryptionServiceProvider::createParametersHash($params), 
            fn(string $hash) => $_REQUEST['hash'] = $hash
        );
    }

    /**
     * Verify request parameters validity.
     * 
     * @return bool
     */
    public function verifyParameters() : bool
    {
        return EncryptionServiceProvider::verifyParameters();
    }

    /**
     * Return current request signature
     * 
     * @return mixed
     */
    public static function getCurrentRequest() : mixed
    {
        return static::$current_request;
    }

    /**
     * Get or set session
     * 
     * @param string $entry Session entry
     * @param ?string $value Entry value
     * @return mixed
     */
    public function session(string $entry, ?string $value = null) : mixed
    {
        if ( isset($value) ) {
            return $_SESSION[$entry] = $value;
        }

        return isset( $_SESSION[$entry] ) ? $_SESSION[$entry]: null;
    }

    /**
     * Get or set cookie
     * 
     * @param string $name Cookie name
     * @param ?string $value Cookie value
     * @param ?int $expiry Default one year
     * @param ?string $path Default root path
     * @return mixed
     */
    public function cookie(string $name, ?string $value = null, ?int $expiry = 604800, ?string $path = '/') : mixed
    {
        if ( ! is_null($value) ) {
            return setcookie($name, $value, time() + $expiry, $path);
        }

        return $_COOKIE[$name];
    }

    /**
     * Return authorization bearer header value
     * 
     * @return string
     */
    public function getToken() : string
    {
        $authorization = $this->getHeader('Authorization');
        
        if ($authorization) {
            return preg_replace('/^(Bearer )/i', '', $authorization);
        }

        return '';
    }

    /**
     * Alias of getToken() method
     * 
     * @return string
     */
    public function bearerToken() : string
    {
        return $this->getToken();
    }

    /**
     * Get authenticated user
     * 
     * @return mixed
     */
    public function user() : mixed
    {
        if ($authenticatorClass = AuthServiceProvider::userAuthenticator()) {
            /** @var \Clicalmani\Fundation\Auth\Authenticate */
            $authenticator = new $authenticatorClass;
            $user_id = $authenticator->getConnectedUserID();

            /**
             * |----------------------------------------------------
             * | Test User
             * |----------------------------------------------------
             * | To interact with the app as a normal user when testing, a user ID
             * | may be specified.
             */
            if ( inConsoleMode() ) $user_id = $this->test_user_id;

            return $authenticator->createUser($user_id);
        }

        return null;
    }

    /**
     * @override
     * @see jsonSerialize()
     */
    public function jsonSerialize() : mixed
    {
        return $_REQUEST;
    }

    /**
     * Make request parameters
     * 
     * @param array $params Parameters
     * @return void
     */
    public function make(array $params = []) : void
    {
        $_REQUEST = $params;
    }

    /**
     * Redirect route
     * 
     * @return \Clicalmani\Fundation\Http\Requests\RequestRedirect
     */
    public function redirect() : RequestRedirect
    {
        return new RequestRedirect;
    }

    /**
     * Request parameter value
     * 
     * @param ?string $param Parameter to request the value. If omitted all the parameters will be returned.
     * @return mixed
     */
    public function request(?string $param = null) : mixed
    {
        return isset($param) ? request($param): request();
    }

    /**
     * Associate each request parameter to its value with an egal sign. Useful for filtering.
     * 
     * @param array $exclude List of parameters to exclude
     * @return array
     */
    public function where(?array $exclude = []) : array
    {
        $exclude[] = 'hash'; // Default
        $filters = [];

        if ( request() ) {
            $filters = collection()->exchange(array_keys(request()))
                            ->filter(function($param) use($exclude) {
                                return ! in_array($param, $exclude);
                            })->map(function($param) {
                                return is_string(request($param)) ? sanitize_attribute($param) . '="' . request($param) . '"': request($param);
                            })->toArray();
        }
        
        return $filters;
    }

    /**
     * Route request
     * 
     * @return \Clicalmani\Fundation\Http\Requests\RequestRoute
     */
    public function route() : RequestRoute
    {
        return new RequestRoute; 
    }
}
