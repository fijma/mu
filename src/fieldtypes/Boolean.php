<?php

namespace Mu;

abstract class Boolean implements \Mu\Fieldtype
{

    abstract public function create($label);

	abstract public function prepare($value);

	public function validate($value)
	{
		return is_bool($value);
	}

	abstract public function convert($value);

}
