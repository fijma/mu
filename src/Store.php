<?php

namespace fijma\Mu;

/**
 * Defines the api for Mu data repositories.
 *
 * Records are arrays of the following form:
 *     [     'id' => integer,            // unique id
 *         'type' => string,             // the record type
 *      'version' => string,             // version id (for optimistic locking and version history)
 *      'deleted' => boolean,            // flags whether record is deleted (no hard deletes)
 *         'data' => ['key' => value...] // array of key/value pairs of record properties
 *     ]
 *
 * Note that implementing classes are permitted to add additional key/value pairs for their
 * own purposes, but this minimal definition will be validated by the Mu api.
 * 
 * Field type arrays are of the following form:
 *     ['fieldtype_name' => 'implementing_class', ...]
 *
 * Record type arrays are of the following form:
 *    ['field' => ['fieldtype_name', (bool)optional], ...]
 * 
 */
interface Store
{

    /**
     * Creates a new record of given type with given data and returns a record array.
     * This function must throw an Exception on failure.
     */
    public function create($type, Array $data);

    /**
     * Returns the record for the given id, or null if id not found.
     */
    public function get($id);

    /**
     * Flags the given record as deleted in the repository.
     * Returns the deleted record.
     * This function must throw an Exception on failure.
     * (You should probably just use the update function to do this.)
     */
    public function delete(Array $record);

    /**
     * Removes the delete flag for the given record.
     * Returns the undeleted record.
     * This function must throw an Exception on failure.
     * (You should probably just use the update function to do this.)
     */
    public function undelete(Array $record);

    /**
     * Performs a version check against the repository, saves the record,
     * and returns the updated record array.
     * This function must throw an Exception on failure.
     */
    public function update(Array $record);

    /**
     * Creates a relationship of the given type between two records.
     * This function must throw an Exception on failure.
     */
    public function relate($relationship_type, $from, $to);

    /**
     * Removes the given relationship.
     * This function must not throw an exception if the defined relationship does not exist.
     * This function must throw an exception if the defined relationship does exist
     * but is not able to be deleted.
     */
    public function unrelate($relationship_type, $from, $to);

    /**
     * Returns an array of the field types registered in this store.
     */
    public function fieldtypes();

    /**
     * Adds a fieldtype definition to the store's registry.
     * This function must throw an exception if the registration fails.
     */
    public function register_fieldtype($fieldtype, $implementing_class);
    
    /**
     * Returns an array of the record types registered in this store.
     */
    public function recordtypes();

    /**
     * Adds a recordtype definition to the store's registry.
     * This function must throw an exception if the registration fails.
     */
    public function register_recordtype($recordtype, Array $fieldtypes);

    /**
     * Returns an array of the search providers registered in this store.
     */
    public function searchers();

    /**
     * Adds a search provider to the store's registry.
     * This function must throw an exception if the registration fails.
     */
    public function register_searcher($name, $implementing_class);

}
