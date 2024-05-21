<?php
namespace Clicalmani\Fundation\Misc;

class File 
{
	private $file_name;
	
    function __construct($name) {
	    $this->file_name = $name;
	}
	
	function inTypes($group = array()) {
	    
		$ext = strtolower(substr($this->file_name, strrpos($this->file_name, '.')+1));
		
		if(in_array($ext, $group)) {
		    return true;
		}
		
		return false;
	}
	
	function isType($ext) {
	
	    return strtolower(substr($this->file_name, strrpos($this->file_name, '.')+1)) === strtolower($ext);
	}
	
	static function delete($path) {
	
	    if (is_dir($path) === true) {
		
		    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::CHILD_FIRST);
			
			foreach ($files as $file) {
			    
				if (in_array($file->getBaseName(), array('.', '..')) !== true) {
				    
					if ($file->isDir() === true) {
					    
						rmdir($file->getPathName());
					} elseif (($file->isFile() === true) || ($file->isLink() === true)) {
					    
						unlink($file->getPathName());
					}
				}
			}
			
			return rmdir($path);
            
		} elseif ((is_file($path) === true) || (is_link($path) === true)) {
		    
			return unlink($path);
		}
		
		return false;
	}
}
?>