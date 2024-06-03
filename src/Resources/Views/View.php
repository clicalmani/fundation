<?php
namespace Clicalmani\Fundation\Resources\Views;

use Clicalmani\Fundation\Sandbox\Sandbox;

class View
{
    /**
     * Render a view
     * 
     * @param string $template
     * @param ?array $vars Variables
     * @return mixed
     */
    public static function render(string $template, ?array $vars = []) : mixed
    {
        $template_path = resources_path("/views/$template.template.php");
        
        if ( file_exists( $template_path ) AND is_readable( $template_path ) ) {
            return @ Sandbox::eval(file_get_contents($template_path), $vars);
        }

        throw new \Clicalmani\Fundation\Exceptions\ResourceViewException('No resource found');
    }
}
