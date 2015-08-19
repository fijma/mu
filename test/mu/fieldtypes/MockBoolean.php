<?php

namespace Mu;

class MockBoolean extends \Mu\Boolean implements \Mu\Fieldtype
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
