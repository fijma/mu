<?php

namespace fijma\Mu;

class MockInteger implements \fijma\Mu\Fieldtype
{

    public function create(string $label): string
    {
        return '';
    }

    public function prepare($value)
    {
        return $value;
    }

    public function convert($value)
    {
        return $value;
    }

    public function validate($value, $optional = false): bool
        {
        if ($optional) {
            return is_null($value) || is_int($value);
        } else {
            return is_int($value);
        }
    }
}