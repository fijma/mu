<?php

namespace Mu;

class Float implements \Mu\Fieldtype
{

    public function prepare($value)
    {
        return $value;
    }

    public function validate($value){
        return is_float($value);
    }

    public function convert($value){
        return $value;
    }
}