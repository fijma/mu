<?php

namespace fijma\Mu;

abstract class DateTime implements \fijma\Mu\Fieldtype
{

    abstract public function create($label);

    abstract public function prepare($value);

    public function validate($value, $optional = false)
    {
        if ($optional) {
            return is_null($value) || $value instanceof \DateTime;
        } else {
            return $value instanceof \DateTime;
        }
    }

    abstract public function convert($value);

}
