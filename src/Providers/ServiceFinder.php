<?php
namespace Clicalmani\Fundation\Providers;

class ServiceFinder extends \RecursiveFilterIterator
{
    public function accept(): bool
    {
        $filename = $this->current()->getFilename();
        $pathname = $this->current()->getPathname();

        if ($filename[0] == '.' || is_dir($pathname) || false == is_readable($pathname)) return false;

        if (strrpos($filename, '.php')) return true;
    }
}