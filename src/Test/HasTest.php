<?php 
namespace Clicalmani\Fundation\Test;

trait HasTest
{
    public static function test(string $action)
    {
        $controller = "App\\Test\\Controllers\\" . substr(self::class, strrpos(self::class, "\\") + 1) . 'Test';
        return with( new $controller )->new($action);
    }
}
