<?php
namespace Clicalmani\Fundation\Http\Response;

/**
 * Class HttpResponse
 * 
 * @package Clicalmani\Fundation
 * @author @Clicalmani\Fundation
 */
class HttpResponse 
{
    /**
     * Send a json response
     * 
     * @param mixed $data
     * @param ?bool $success Default false
     * @return string|false
     */
    public static function json(mixed $data, ?bool $success = false) : string|false
    {
        return json_encode(
            [
                'success' => $success,
                'data'    => $data
            ],
            JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK |
            JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR /** Enable strict mode */,
            512
        );
    }

    /**
     * Send a status response
     * 
     * @param int $code Status code
     * @return int|false
     */
    public function sendStatus(int $code) : int|false
    {
        return http_response_code($code);
    }

    /**
     * Send a success status
     * 
     * @param mixed $success_message
     * @return string|false
     */
    public static function success(mixed $success_message = null) : string|false
    {
        return self::json($success_message, true);
    }

    /**
     * Send an error status
     * 
     * @param mixed $error_message
     * @return string|false
     */
    public static function error(mixed $error_message = null) : string|false
    {
        return self::json($error_message, false);
    }
}
