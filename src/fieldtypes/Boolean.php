<?php

namespace fijma\Mu;

abstract class Boolean implements \fijma\Mu\Fieldtype
{

    abstract public function create($label);

	abstract public function prepare($value);

	public function validate($value, $optional = false)
	{
        if ($optional) {
            return is_null($value) || is_bool($value);
        } else {
		    return is_bool($value);
        }
	}

	abstract public function convert($value);

}
