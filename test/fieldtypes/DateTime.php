<?php

namespace Mu;

class DateTime implements \Mu\Fieldtype
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
        return $value instanceof \DateTime;
    }

    public function convert($value)
    {
        return $value;
    }

}
