<?php

namespace Mu;

abstract class DateTime implements \Mu\Fieldtype
{

    abstract public function create($label);

    abstract public function prepare($value);

    public function validate($value)
    {
        return $value instanceof \DateTime;
    }

    abstract public function convert($value);

}
