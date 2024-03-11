<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Flesco\Support\Log;
use Clicalmani\Fundation\Validation\InputValidator;

class IDValidator extends InputValidator
{
    protected string $argument = 'id';

    public function options() : array
    {
        return [
            'model' => [
                'required' => true,
                'type' => 'string',
                'function' => fn(string $model) => collection(explode('_', $model))->map(fn(string $part) => ucfirst($part))->join('')
            ],
            'primary' => [
                'required' => true,
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
        $model = "\\App\\Models\\" . $options['model'];
        $primaryKey = $options['primary'];

        if ( class_exists($model) ) {
            if ( is_array($primaryKey) ) $value = explode(',', $value);
            if ($model = $model::find($value)) {
                return $model->{$primaryKey} == $value;
            }
                
        }

        return false;
    }
}
