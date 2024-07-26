<?php
namespace Clicalmani\Fundation\Sandbox;

class Sandbox
{
    private static $tmp_name = '__.php';

    public static function eval($exec, $args) {
        $args     = serialize($args);

        $content = <<<EVAL
        <?php
        \$serialized = <<<ARGS
        $args
        ARGS;
        extract(unserialize(\$serialized));

        return <<<DELIMITER
            $exec
        DELIMITER;
        EVAL;
        
        return self::getResult($content);
    }

    /**
     * Evaluate a PHP operation
     * 
     * @param string $operation
     * @return mixed
     */
    public static function calc(string $operation) : mixed
    {
        $content = <<<EVAL
        <?php return $operation; ?>
        EVAL;

        return self::getResult($content);
    }

    private static function getResult(string $content)
    {
        file_put_contents(sys_get_temp_dir() . '/' . static::$tmp_name, $content);
        return include sys_get_temp_dir() . '/' . static::$tmp_name;
    }
}
