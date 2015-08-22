<?php

namespace fijma\Mu;

abstract class DateTime implements \fijma\Mu\Fieldtype
{

    abstract public function create($label);

    abstract public function prepare($value);

    public function validate($value)
    {
        return $value instanceof \DateTime;
    }

    abstract public function convert($value);

}
