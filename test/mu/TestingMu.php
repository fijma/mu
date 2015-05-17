<?php

use \Mu\Mu;

class TestingMu extends Mu
{
    public function store()
    {
        return get_class($this->store);
    }

    public function test_validation($recordtype, $data)
    {
        return $this->validate($recordtype, $data);
    }
}