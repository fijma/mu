<?php

namespace Mu;

class Integer implements \Mu\Fieldtype
{

    public function create($label)
    {
        return null;
    }

    public function prepare($value)
    {
        return $value;
    }

    public function validate($value)
    {
        return is_int($value);
    }

    public function convert($value)
    {
        return $value;
    }
}