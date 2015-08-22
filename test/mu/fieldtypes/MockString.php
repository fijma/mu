<?php

namespace fijma\Mu;

class MockString extends \fijma\Mu\String implements \fijma\Mu\Fieldtype
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