<?php

namespace Mu;

abstract class String implements \Mu\Fieldtype
{
    
    abstract public function create($label);

    abstract public function prepare($value);

    public function validate($value)
    {
        return is_string($value);
    }

    abstract public function convert($value);
}