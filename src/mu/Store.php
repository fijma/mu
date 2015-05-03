<?php

namespace Mu;

/**
 * Defines the api for Mu data repositories.
 *
 * Records are arrays of the following form:
 *     [     'id' => integer,            // unique id
 *         'type' => string,             // the record type
 *      'version' => string,             // version id (for optimistic locking)
 *      'deleted' => boolean,            // flags whether record is deleted (no hard deletes)
 *         'data' => ['key' => value...] // array of key/value pairs of record properties
 *     ]
 *
 */
abstract class Store
{

    /**
     * Creates a new record of given type with given data and returns a record array.
     * This function must throw an Exception on failure.
     */
    abstract public function create($type, Array $data);

    /**
     * Returns the record for the given id, or null if id not found.
     */
    abstract public function get($id);

    /**
     * Flags the given record as deleted in the repository.
     * Returns the deleted record.
     * This function must throw an Exception on failure.
     * (You should probably just use the update function to do this.)
     */
    abstract public function delete(Array $record);

    /**
     * Performs a version check against the repository, saves the record,
     * and returns the updated record array.
     * This function must throw an Exception on failure.
     */
    abstract public function update(Array $record);

}