<?php
namespace Clicalmani\Fundation\Http\Response;

use Clicalmani\Fundation\Http\Requests\Request;

/**
 * Trait JsonResponse
 * 
 * @package Clicalmani\Fundation
 * @author @Clicalmani\Fundation
 */
Trait WebResponse
{
    /**
     * 404 Not found redirect
     * 
     * @return mixed
     */
    public function notFound() : mixed
    {
        $this->sendStatus(404);

        /**
         * |------------------------------------------------------------
         * | Default 404
         * |------------------------------------------------------------
         */

        echo view('404');
        exit;
    }

    /**
     * 401 Unauthorized redirect
     * 
     * @return mixed
     */
    public function unauthorized() : mixed
    {
        $this->sendStatus(401);
        
        /**
         * |------------------------------------------------------------
         * | Default 401
         * |------------------------------------------------------------
         */

        echo view('401');
        exit;
    }

    /**
     * 403 Forbiden redirect
     * 
     * @return mixed
     */
    public function forbiden() : mixed
    {
        $this->sendStatus(403);

        /**
         * |------------------------------------------------------------
         * | Default 403
         * |------------------------------------------------------------
         */

         echo view('403');
        exit;
    }

    /**
     * 500 Internal server error
     * 
     * @return mixed
     */
    public function internalServerError() : mixed
    {
        $this->sendStatus(500);

        /**
         * |------------------------------------------------------------
         * | Default 500
         * |------------------------------------------------------------
         */

         echo view('500');
        exit;
    }

    /**
     * 400 bad request redirect
     * 
     * @return mixed
     */
    public function bad_request() : mixed
    {
        $this->sendStatus(400);

        /**
         * |------------------------------------------------------------
         * | Default 400
         * |------------------------------------------------------------
         */

         echo view('400');
        exit;
    }

    /**
     * Send a custom response
     * 
     * @return mixed
     */
    public function send(int $response_code) : mixed
    {
        $this->sendStatus($response_code);

        /**
         * |------------------------------------------------------------
         * | Custom Status
         * |------------------------------------------------------------
         */
        $template_path = resources_path("/views/$response_code.template.php");
        
        if ( file_exists( $template_path ) AND is_readable( $template_path ) ) {
            echo view($response_code);
        }
        
        exit;
    }
}