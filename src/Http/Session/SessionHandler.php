<?php
namespace Clicalmani\Fundation\Http\Session;

use Clicalmani\Fundation\Auth\EncryptionServiceProvider;

/**
 * SessionHandler Class
 * 
 * @package clicalmani/fundation
 * @author @clicalmani
 */
abstract class SessionHandler implements \SessionHandlerInterface
{
    protected bool $encrypt = false;
    protected string $table = '';

    public function __construct(bool $encrypt, ?array $flags = [])
    {
        $this->encrypt = $encrypt;
        $this->table = $flags['table'];
    }

    /**
     * Encrypt data
     * 
     * @param string $data
     * @return string
     */
    protected function encrypt(string $data) : string
    {
        return $this->encrypt ? EncryptionServiceProvider::encrypt($data): base64_encode($data);
    }

    /**
     * Decrypt data
     * 
     * @param string $data
     * @return string
     */
    protected function decrypt(string $data) : string
    {
        return $this->encrypt ? EncryptionServiceProvider::decrypt($data): base64_decode($data);
    }

    public static function getIdPrefix()
    {
        return 'tonka-';
    }
}
