<?php

namespace fijma\Mu;

class MockInteger extends \fijma\Mu\Integer implements \fijma\Mu\Fieldtype
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