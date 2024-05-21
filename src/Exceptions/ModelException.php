<?php
namespace Clicalmani\Fundation\Exceptions;

class ModelException extends \Exception 
{
	/**
	 * ------------------------------------------------------
	 * 				***** Error Codes *****
	 * ------------------------------------------------------
	 */

	/**
	 * Values count does not match fields count.
	 * 
	 * @var int 3050
	 */
	const ERROR_3050 = 3050;

	/**
	 * BulK update or delete
	 * 
	 * @var int 3060
	 */
	const ERROR_3060 = 3060;

	/**
	 * Unknow attribute
	 * 
	 * @var int 3070
	 */
	const ERROR_3070 = 3070;

	public function __construct(?string $message = '', ?int $status_code = 0, ?\Throwable $previous = null)
	{
		parent::__construct($message, $status_code, $previous);
	}
}
