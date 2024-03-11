<?php
namespace Clicalmani\Fundation\Validation;

trait ParseValidator
{
    public function getArguments()
    {
        return collection( explode('|', $this->signature) )
                ->filter(fn(string $argument) => preg_match('/^[0-9a-z\[\]]+$/', $argument));
    }

    public function getOptions()
    {
        $options = collection( explode('|', $this->signature) )
                        ->filter(fn(string $argument) => ! in_array($argument, array_merge($this->defaultArguments, [$this->argument])))
                        ->map(function(string $option) {
                            @[$opt, $value] = explode(':', $option);
                            return [$opt, $value];
                        });

        $ret = [];

        foreach ($options as $option) {
            $ret[$option[0]] = $option[1];
        }

        return $ret;
    }

    public function getArgumentOptions(string $name)
    {
        $this->argument = $name;

        if ( -1 !== $this->getArguments()->index($name) ) return $this->getOptions();

        return null;
    }
}
