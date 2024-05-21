<?php
namespace Clicalmani\Fundation\Support;

/**
 * Class Helper
 * 
 * @package Clicalmani\Fundation
 * @author @Clicalmani\Fundation
 */
class Helper 
{
    /**
     * Include helper functions
     * 
     * @return void
     */
    public static function include()
    {
        include_once dirname( __DIR__ ) . '/helpers.php';
    }
}
