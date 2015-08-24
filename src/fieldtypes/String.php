<?php

namespace fijma\Mu;

abstract class String implements \fijma\Mu\Fieldtype
{
    
    abstract public function create($label);

    abstract public function prepare($value);

    public function validate($value, $optional = false)
    {
        if ($optional) {
            return is_null($value) || is_string($value) || $value === '';
        } else {
            return is_string($value);
        }
    }

    abstract public function convert($value);
}