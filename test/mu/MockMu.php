<?php

use fijma\Mu\Mu;

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

    public function test_validation($record_type, $data)
    {
        return $this->validate($record_type, $data);
    }

    public function show_me_your_deregistered_field_types()
    {
        return $this->deregistered_field_types;
    }

    public function show_me_your_deregistered_record_types()
    {
        return $this->deregistered_record_types;
    }

    public function test_validate_find_parameters($record_type, $parameters)
    {
        return $this->validate_find_parameters($record_type, $parameters);
    }

}