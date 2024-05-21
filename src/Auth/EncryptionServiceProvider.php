<?php
namespace Clicalmani\Fundation\Auth;

class EncryptionServiceProvider 
{
	/**
	 * Password hashing
	 * 
	 * @var mixed
	 */
	private static $config;

	public function boot()
	{
		if (!static::$config) static::$config = require_once config_path('/hashing.php');
	}

	/**
	 * Generate a data hash
	 * 
	 * @param mixed $data
	 * @param ?string $method
	 * @return mixed
	 */
    public static function hash(mixed $data) : mixed
	{
		$config = static::$config;
		$method = @ $config['algo'];

		$__func = fn($str) => hash($method, $str);
		$__secret = env('APP_KEY');
    	
    	$_ipad = substr($__secret, strlen($__secret), 0) ^ str_repeat(chr(0x36), strlen($__secret));
    	$__opad = substr($__secret, 0, strlen($__secret)) ^ str_repeat(chr(0x5C), strlen($__secret));
    	
    	$__inner = @ pack('H32', $__func($_ipad . $data));
    	$__digest = $__func($__opad . $__inner);
    	
    	return $__digest;
    }
    
	/**
	 * Create parameters hash
	 * 
	 * @param array $params
	 * @return string
	 */
    public static function createParametersHash(array $params) : string
	{
    	$data = '';

    	foreach ($params as $key => $value){
    		$data .= $key . $value;
    	}
    	
    	return strtoupper( substr( self::hash($data), strlen( self::iv() ), static::$config['hash_length'] ) );
    }

	/**
	 * Verify parameters
	 * 
	 * @return bool
	 */
    public static function verifyParameters() : bool
	{
    	$data = '';
		$param = self::hashParameter();
		
    	$request_hash = isset($_REQUEST[$param])? $_REQUEST[$param]: '';
		
    	unset($_REQUEST[$param]);
    	 
    	foreach ($_REQUEST as $key => $value){
    		$data .= $key . $value;
    	}
		
		$hash = strtoupper( substr( self::hash($data), strlen( self::iv() ), static::$config['hash_length'] ) );
    	
    	if($request_hash === $hash){
    		return true;
    	}

		return false;
    }

	/**
	 * Create iv
	 * 
	 * @return string
	 */
	public static function iv() : string
	{
		return substr( hash(static::$config['algo'], env('APP_KEY')), 0, static::$config['iv_length']);
	}
	
	/**
	 * Openssl Encrypt or decrypt a string
	 * 
	 * @param string $action
	 * @param string $string
	 * @return mixed
	 */
    public static function opensslED(string $action, string $string) : mixed
	{
	
		$output = false;
		$encrypt_method = static::$config['cipher'];
		
		// hash
		$key = hash(static::$config['algo'], $_ENV['APP_KEY']);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = self::iv();

		if ( $action == 'encrypt' ) {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		} else if( $action == 'decrypt' ) {
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}

		return $output;
	}
    
	/**
	 * Encrypt a value
	 * 
	 * @param string $value
	 * @return mixed
	 */
    public static function encrypt(string $value) { return self::opensslED('encrypt', $value); }

	/**
	 * Decrypt an encrypted value
	 * 
	 * @param string $value
	 * @return mixed
	 */
    public static function decrypt(string $value) : mixed { return self::opensslED('decrypt', $value); }

	/**
	 * PHP built-in password_hash wrapper
	 * 
	 * @param string $str
	 * @return string
	 */
	public static function password(string $str) : string
	{
		$config = static::$config;

		switch($config['driver']) {
			case 'bcrypt': 
				$__func = fn($str) => password_hash($str, PASSWORD_BCRYPT, ['cost' => @ $config['bcrypt']['cost'] ?? PASSWORD_BCRYPT_DEFAULT_COST]);
				break;

			case 'argon': 
				if ($config['argon']['2i']) $algo = PASSWORD_ARGON2I;
				else $algo = PASSWORD_ARGON2ID;

				$__func = fn($str) => password_hash(
					$str, 
					$algo, 
					[
						'memory' => @ $config['argon']['memory'] ?? PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
						'threads' => @ $config['argon']['threads'] ?? PASSWORD_ARGON2_DEFAULT_THREADS,
						'time' => @ $config['argon']['time'] ?? PASSWORD_ARGON2_DEFAULT_TIME_COST
					]);
				break;

			default:
				$__func = fn($str) => password_hash($str, PASSWORD_DEFAULT);
				break;
		}

		return $__func($str);
	}

	/**
	 * Returns hash parameter name
	 * 
	 * @return string|null
	 */
	public static function hashParameter() : string|null
	{
		return @ static::$config['hash_parameter'];
	}
}
