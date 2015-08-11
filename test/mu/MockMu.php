<?php

use \Mu\Mu;

class MockMu extends Mu
{
    public function store()
    {
        return get_class($this->store);
    }

    public function test_record_validation($record)
    {
        return $this->validate_record($record);
    }

    public function test_validation($recordtype, $data)
    {
        return $this->validate($recordtype, $data);
    }
}