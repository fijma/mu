<?php

namespace fijma\Mu;

/**
 * The Search interface allows you to implement your own search algorithms for the standard search api.
 * Alternately, you could extend the base Search class to add your own custom search functions.
 * Search objects are registered in the Store similarly to field types.
 * All functions return an array of records (or an empty array if no records found).
 * Functions should throw exceptions if they receive invalid parameters.
 * All parameters should be optional.
 */

interface Searcher
{

    /**
     * Returns all entries of the given $record_type.
     * Params are:
     *     - filter: array of [field => criteria] tuples to filter the results by.
     *     - order: array of [field => boolean] tuples to sort the results by. True indicates ascending, false indicates descending.
     *     - limit: limit the number of results to return.
     *     - offset: defines the number of results to be skipped.
     *     - deleted: filters on whether records are deleted (true) or not (false). If omitted, all records are returned.
     */
    public function find($record_type, $params = []);

    /**
     * Returns all records which share a relationship with the record defined by $record_id.
     * Params are:
     *     - relationship_type: the type of relationship to return.
     *     - direction: the direction of the relationship. 'to' indicates outgoing relationships with respect to the given $record_id.
     *     - record_type: filters results to only those records matching the given record_type.
     *     - filter: array of [field => criteria] tuples to filter the results by. Only valid if record_type has been specified.
     *     - deleted: filters on whether records are deleted (true) or not (false). If omitted, all records are returned.
     */
    public function related($record_id, $params = []);

}
