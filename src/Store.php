<?php

namespace fijma\Mu;

/**
 * Defines the api for Mu data repositories.
 *
 * Records are arrays of the following form:
 *     [     'id' => any data type,      // unique id
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
 *     ['field_type_name' => 'implementing_class', ...]
 *
 * Record type arrays are of the following form:
 *    ['field' => ['field_type_name', (bool)optional], ...]
 * 
 */
interface Store
{

    /**
     * Creates a new record of given type with given data and returns a record array.
     * This function must throw an Exception on failure.
     */
    public function create(string $type, array $data): array;

    /**
     * Returns the record for the given id, or null if id not found.
     * This function must use the field_types->convert() function to get the php version of the data.
     * Note that the store must support registered as well as deregistered field types and record types
     * when retrieving existing records.
     */
    public function get($id);

    /**
     * Flags the given record as deleted in the repository.
     * Returns the deleted record.
     * This function must throw an Exception on failure.
     * (You should probably just use the update function to do this.)
     */
    public function delete(array $record): array;

    /**
     * Removes the delete flag for the given record.
     * Returns the undeleted record.
     * This function must throw an Exception on failure.
     * (You should probably just use the update function to do this.)
     */
    public function undelete(array $record): array;

    /**
     * Performs a version check against the repository, saves the record,
     * and returns the updated record array.
     * This function must throw an Exception on failure.
     */
    public function update(array $record): array;

    /**
     * Creates a relationship of the given type between two records.
     * This function must throw an Exception on failure.
     * Note $from and $to represent the record ids, not the records themselves.
     * Returns true if the relationship is created, false if the relationship already existed.
     */
    public function relate(string $relationship_type, $from, $to): bool;

    /**
     * Removes the given relationship.
     * This function must not throw an exception if the defined relationship does not exist.
     * This function must throw an exception if the defined relationship does exist
     * but is not able to be deleted.
     * Note $from and $to represent the record ids, not the records themselves.
     * Returns true if the relationship is removed, false if the relationship never existed.
     */
    public function unrelate(string $relationship_type, $from, $to): bool;

    /**
     * Returns an array of the field types registered in this store.
     */
    public function field_types(): array;

    /**
     * Returns an array of the deregistered field types in this store.
     */
    public function deregistered_field_types(): array;

    /**
     * Adds a field_type definition to the store's registry.
     * This function must throw an exception if the registration fails.
     */
    public function register_field_type(string $field_type, string $implementing_class);

    /**
    * Removes a field_type definition from the store's registry.
    * This function must throw an exception if the deregistration fails.
    * Deregistration should not prevent a record using the field_type from being retrieved.
    * It should prevent all other actions for a record using that field_type.
    */
    public function deregister_field_type(string $field_type);
    
    /**
     * Returns an array of the record types registered in this store.
     */
    public function record_types(): array;

    /**
     * Returns an array of the deregistered record types in this store.
     */
    public function deregistered_record_types(): array;

    /**
     * Adds a record_type definition to the store's registry, and creates the necessary infrastructure to store records of that type.
     * This function must throw an exception if the registration fails.
     */
    public function register_record_type(string $record_type, Array $field_types);

    /**
     * Removes a record_type defintion from the store's registry.
     * This function must throw an exception if the deregistration fails.
     * Deregistration should not prevent a record of the deregistered record_type from being retrieved.
     * It should prevent all other actions for a record of that record_type.
     */
    public function deregister_record_type(string $record_type);

    /**
     * Returns all entries of the given $record_type.
     * Parameters are:
     *     - filter: array of [field => criteria] tuples to filter the results by.
     *     - order: array of [field => boolean] tuples to sort the results by. True indicates ascending, false indicates descending.
     *     - limit: limit the number of results to return.
     *     - offset: defines the number of results to be skipped.
     *     - deleted: filters on whether records are deleted (true) or not (false). If omitted, all records are returned.
     *
     * It is a decision for the implementer whether filters must be exact matches, and how to deal with non-scalars.
     * The resulting array should be in the form [$id => [$record], ...]
     */
    public function find(string $record_type, array $params = []): array;

    /**
     * Returns all records which share a relationship with the record defined by $record_id.
     * Parameters are:
     *     - relationship_type: filters results to only those relationships matching this type.
     *     - direction: the direction of the relationship. 'to' indicates incoming relationships with respect to the given $record_id. If omitted, all relationships are returned.
     *     - record_type: filters results to only those related records matching this type.
     *     - filter: array of [field => criteria] tuples to filter the results by. Only valid if record_type has been specified.
     *     - deleted: filters on whether records are deleted (true) or not (false). If omitted, all records are returned.
     *
     * This function must throw an exception if the $record_id does not exist.
     * The resulting array should be in the form [$id => [$record], ...]
     */
    public function related($record_id, array $params = []): array;

    /**
     * Returns the version history for the given record.
     * This function has no parameters.
     */
    public function versions($record_id): array;
}
