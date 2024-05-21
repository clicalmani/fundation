<?php
namespace Clicalmani\Fundation\Maker;

/**
 * Make an application
 * 
 * @package Clicalmani\Fundation
 * @author Clicalmani\Fundation
 */
class Application extends \Symfony\Component\Console\Application
{
    public function __construct(private $root_path = null)
    {
        parent::__construct();
    }

    public function make()
    {
        // Console Kernel
        $kernel = \Clicalmani\Console\Kernel::$kernel;

        foreach ($kernel as $command) {
            $this->add(new $command($this->root_path));
        }
    }
}
