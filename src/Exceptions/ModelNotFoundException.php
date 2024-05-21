<?php
namespace Clicalmani\Fundation\Exceptions;

class ModelNotFoundException extends \Exception {
	function __construct($model = ''){
		parent::__construct("The specified model $model could not been found.");
	}
}
