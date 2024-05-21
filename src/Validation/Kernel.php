<?php
namespace Clicalmani\Fundation\Validation;

class Kernel {

    /**
     * Return input validators
     * 
     * @return string[]
     */
    public function validators() : array
    {
        return [
            \Clicalmani\Fundation\Validation\Validators\BoolValidator::class,
            \Clicalmani\Fundation\Validation\Validators\BooleanValidator::class,
            \Clicalmani\Fundation\Validation\Validators\DateTimeValidator::class,
            \Clicalmani\Fundation\Validation\Validators\DateValidator::class,
            \Clicalmani\Fundation\Validation\Validators\EmailValidator::class,
            \Clicalmani\Fundation\Validation\Validators\EnumValidator::class,
            \Clicalmani\Fundation\Validation\Validators\FloatValidator::class,
            \Clicalmani\Fundation\Validation\Validators\IDValidator::class,
            \Clicalmani\Fundation\Validation\Validators\IntValidator::class,
            \Clicalmani\Fundation\Validation\Validators\IntegerValidator::class,
            \Clicalmani\Fundation\Validation\Validators\NumberValidator::class,
            \Clicalmani\Fundation\Validation\Validators\NumbersValidator::class,
            \Clicalmani\Fundation\Validation\Validators\NumericValidator::class,
            \Clicalmani\Fundation\Validation\Validators\NumericsValidator::class,
            \Clicalmani\Fundation\Validation\Validators\ObjectValidator::class,
            \Clicalmani\Fundation\Validation\Validators\ObjectsValidator::class,
            \Clicalmani\Fundation\Validation\Validators\RegExpValidator::class,
            \Clicalmani\Fundation\Validation\Validators\StringValidator::class,
            \Clicalmani\Fundation\Validation\Validators\StringsValidator::class,
            \Clicalmani\Fundation\Validation\Validators\UriValidator::class,
            \Clicalmani\Fundation\Validation\Validators\NavigationGuardValidator::class,
        ];
    }
};
