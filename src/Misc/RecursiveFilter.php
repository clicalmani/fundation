<?php 
namespace Clicalmani\Fundation\Misc;

class RecursiveFilter extends \RecursiveFilterIterator
{
    private $filter = [];

    private $pattern;

    private $types = [];

    public function accept(): bool
    {
        $filename = $this->current()->getFilename();
        $pathname = $this->current()->getPathname();
        
        if ($filename[0] == '.' || is_dir($pathname) || false == is_readable($pathname)) return false;

        $filename = substr($filename, strrpos($filename, DIRECTORY_SEPARATOR));
        $pathname = substr($pathname, strrpos($pathname, DIRECTORY_SEPARATOR));
        
        if ($this->filter || $this->pattern || $this->types) {
            
            if ($this->filter) {
                if (false == in_array($filename, $this->filter)) return false;
            }

            if ($this->pattern) {
                if ($this->current()->isFile()) {
                    if (preg_match("/$this->pattern/", $filename)) return true;
                }
            }

            if ($this->types) {
                foreach ($this->types as $extension) {
                    if (strrpos($filename, $extension)) return true;
                }
            } 

            return false;
        }

        return true;
    }

    public function getFiles()
    {
        $files = [];

        foreach (new \RecursiveIteratorIterator($this) as $file) { 
            $pathname = $file->getPathname();

                if($file->isFile()) {
                    $filename = $file->getFileName(); 

                    if(is_readable($pathname)) {
                        $files[] = [
                            'name' => $filename,
                            'path' => $pathname
                        ];
                    }
                }
        }

        return $files;
    }

    public function setPattern(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    public function setFilter(array $filter)
    {
        $this->filter = $filter;
    }
}
