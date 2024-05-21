<?php
namespace Clicalmani\Fundation\Exceptions;

class HttpRequestException extends \Exception {
	function __construct($message){
		parent::__construct($message);
	}
}
