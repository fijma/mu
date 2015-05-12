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
 * Field type arrays are of the following form:
 *     ['fieldtype_name' => 'implementing_class', ...]
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

    /**
     * Creates a relationship of the given type between two records.
     * This function must throw an Exception on failure.
     */
    abstract public function relate($relationship_type, $from, $to);

    /**
     * Removes the given relationship.
     * This function must not throw an Exception if the defined relationship does not exist.
     * This function must throw an Exception if the defined relationship does exist
     * but is not able to be deleted.
     */
    abstract public function unrelate($relationship_type, $from, $to);

    /**
     * Returns an array of the field types supported by this store.
     */
    abstract public function field_types();
}