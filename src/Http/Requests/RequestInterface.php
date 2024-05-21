<?php
namespace Clicalmani\Fundation\Http\Requests;

interface RequestInterface 
{
    
    /**
     * (non-PHPDoc)
     * @override
     * 
     * Request signatures
     */
    public function signatures();

    /**
     * (non-PHPDoc)
     * @override
     * 
     * Validate
     */
    public function validate();

    /**
     * (non-PHPDoc)
     * @override
     * 
     * Prepare for validation
     */
    public function prepareForValidation();

    /**
     * (non-PHPDoc)
     * @override
     * 
     * Authorize
     */
    public function authorize();
}