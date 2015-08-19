<?php

namespace Mu;

abstract class Float implements \Mu\Fieldtype
{

    abstract public function create($label);

    abstract public function prepare($value);

    public function validate($value)
    {
        return is_float($value);
    }

    abstract public function convert($value);
}