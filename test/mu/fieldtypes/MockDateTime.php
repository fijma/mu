<?php

namespace fijma\Mu;

class MockDateTime implements \fijma\Mu\Fieldtype
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

    public function validate($value, $optional = false)
    {
        if ($optional) {
            return is_null($value) || $value instanceof \DateTime;
        } else {
            return $value instanceof \DateTime;
        }
    }

}
