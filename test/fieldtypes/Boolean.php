<?php

namespace Mu;

class Boolean implements \Mu\Fieldtype
{

    public function create($label)
    {
        return null;
    }

	public function prepare($value)
	{
		return $value;
	}

	public function validate($value)
	{
		return is_bool($value);
	}


	public function convert($value)
	{
		return $value;
	}

}