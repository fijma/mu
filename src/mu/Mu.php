<?php

namespace Mu;


/**
 * Defines the api, performs version checks and data validation.
 */
class Mu
{

    // The \Mu\Store instance used to access the repository.
    protected $store;

    public function __construct(\Mu\Store $store)
    {
        $this->store = $store;
    }

    // Report the class of the store instance for this Mu instance.
    public function store()
    {
        return get_class($this->store);
    }

    // Creates a new record. See \Mu\Store for documentation.
    public function create($type, Array $data)
    {
        return $this->store->create($type, $data);
    }

    // Returns the record for the given id. See \Mu\Store for documentation.
    public function get($id)
    {
        return $this->store->get($id);
    }

    // Deletes the given record. See \Mu\Store for documentation.
    public function delete(Array $record)
    {
        return $this->store->delete($record);
    }

    // Updates the given record. See \Mu\Store for documentation.
    public function update(Array $record)
    {
        return $this->store->update($record);
    }

    // Creates a relationship between two records. See \Mu\Store for documentation.
    public function relate($relationship_type, $from, $to)
    {
        $this->store->relate($relationship_type, $from, $to);
    }

    // Removes a relationship between two records. See \Mu\Store for documentation.
    public function unrelate($relationship_type, $from, $to)
    {
        $this->store->unrelate($relationship_type, $from, $to);
    }

}