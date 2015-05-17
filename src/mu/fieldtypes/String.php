<?php

namespace Mu;

class String implements \Mu\Fieldtype
{
    public function prepare($value)
    {
        return $value;
    }

    public function validate($value)
    {
        return is_string($value);
    }

    public function convert($value)
    {
        return $value;
    }
}