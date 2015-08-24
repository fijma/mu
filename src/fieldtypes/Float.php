<?php

namespace fijma\Mu;

abstract class Float implements \fijma\Mu\Fieldtype
{

    abstract public function create($label);

    abstract public function prepare($value);

    public function validate($value, $optional = false)
    {
        if ($optional) {
            return is_null($value) || is_float($value);
        } else {
            return is_float($value);
        }
    }

    abstract public function convert($value);
}