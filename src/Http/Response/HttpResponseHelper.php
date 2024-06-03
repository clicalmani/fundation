<?php
namespace Clicalmani\Fundation\Http\Response;

/**
 * Class HttpResponseHelper
 * 
 * @package Clicalmani\Fundation
 * @author @Clicalmani\Fundation
 */
class HttpResponseHelper 
{
    use JsonResponse;
    use WebResponse;
    
    /**
     * Send a status code
     * 
     * @param int $status_code
     * @return int|bool
     */
    public function statusCode(int $status_code) : int|bool
    {
        return http_response_code($status_code);
    }
}
