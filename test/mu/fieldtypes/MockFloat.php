<?php

namespace Mu;

class MockFloat extends \Mu\Float implements \Mu\Fieldtype
{

    public function create($label)
    {
        return null;
    }

    public function prepare($value)
    {
        return $value;
    }

    public function convert($value){
        return $value;
    }
}