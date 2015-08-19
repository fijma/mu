<?php

namespace Mu;

abstract class Integer implements \Mu\Fieldtype
{

    abstract public function create($label);

    abstract public function prepare($value);

    public function validate($value)
    {
        return is_int($value);
    }

    abstract public function convert($value);
}