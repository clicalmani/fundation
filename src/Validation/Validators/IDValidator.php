<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class IDValidator extends InputValidator
{
    protected string $argument = 'id';

    /**
     * ID model
     * 
     * @var string
     */
    protected string $model;

    /**
     * Model primary key
     * 
     * @var string|string[]
     */
    protected $primaryKey;

    public function options() : array
    {
        return [
            'model' => [
                'required' => true,
                'type' => 'string',
                'function' => fn(string $model) => collection(explode('_', $model))->map(fn(string $part) => ucfirst($part))->join('')
            ],
            'primary' => [
                'required' => false,
                'type' => 'string',
                'function' => function(string $primary) {
                    if ( strpos($primary, ',') ) $primary = explode(',', $primary);
                    return $primary;
                }
            ]
        ];
    }

    public function validate(mixed &$value, ?array $options = []) : bool
    {
        $this->model = "\\App\\Models\\" . $options['model'];
        /** @var \Clicalmani\Database\Factory\Models\Model */
        $instance = $this->model::find($value);
        $this->primaryKey = @ $options['primary'] ? $options['primary']: $instance->getKey();
        
        if ( class_exists($this->model) ) {
            
            if ( is_array($this->primaryKey) ) $value = explode(',', $value);

            if (NULL === $this->model::find($value)) return false;

            return true;  
        }

        return false;
    }
}
