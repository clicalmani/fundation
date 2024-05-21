<?php
namespace Clicalmani\Fundation\Sandbox;

class Sandbox
{
    static function eval($exec, $args) {
        $args     = serialize($args);
        $tmp_name = '__.php';

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
        
        file_put_contents(sys_get_temp_dir() . '/' . $tmp_name, $content);

        return include sys_get_temp_dir() . '/' . $tmp_name;
    }
}
