<?php
namespace Clicalmani\Fundation\Exceptions;

class ValidationFailedException extends \Exception 
{
	private $key, $is_required, $redirect_back;

	function __construct($key, $is_required = false, $redirect = false){
		parent::__construct("Parameter $key is not valid");

		$this->key           = $key;
		$this->is_required   = $is_required;
		$this->redirect_back = $redirect;
	}

	function getParameter()
	{
		return $this->key;
	}

	function isRequired() 
	{
		return $this->is_required;
	}

	function redirectBack()
	{
		return $this->redirect_back;
	}
}
