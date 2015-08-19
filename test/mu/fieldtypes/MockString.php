<?php

namespace Mu;

class MockString extends \Mu\String implements \Mu\Fieldtype
{
    
    public function create($label)
    {
        return null;
    }

    public function prepare($value)
    {
        return $value;
    }

    public function convert($value)
    {
        return $value;
    }
}