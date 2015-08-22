<?php

namespace fijma\Mu;

/**
 * Field types handle the conversion of php data to/from the underlying
 * repository. As such, they are intended to be coupled to a particular
 * Store instance.
 */

interface FieldType
{

    /**
     * Returns a string for creating an item of this type in the underlying repository.
     */
    public function create($label);


    /**
     * Returns $value in a form suitable for storage in the underlying repository.
     * This function is complemented by the convert() function.
     */
    public function prepare($value);


    /**
     * Returns a boolean indicating whether $value meets the definition of the FieldType.
     */
    public function validate($value);

    
    /**
     * Converts $value from its representation in the underlying repository to its php.
     * This function is complemented by the prepare() function.
     */
    public function convert($value);

}
