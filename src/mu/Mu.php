<?php

namespace Mu;


/**
 * Defines the api, performs version checks and data validation.
 */
class Mu
{

    // The api version
    private $version = '0.9.0';

    // The \Mu\Store instance used to access the repository.
    protected $store;

    // The fieldtypes supported by the repository.
    protected $fieldtypes = array();

    // The recordtypes supported by the repository.
    protected $recordtypes = array();

    public function __construct(\Mu\Store $store)
    {
        $this->store = $store;
        $this->load_fieldtypes();
        $this->load_recordtypes();
    }

    public function version()
    {
        return $this->version;
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

    // Instantiates the fieldtype objects into the fieldtypes array.
    protected function load_fieldtypes()
    {
        $fieldtypes = $this->store->fieldtypes();
        foreach ($fieldtypes as $fieldtype => $implementing_class) {
            $this->fieldtypes[$fieldtype] = new $implementing_class();
        }
    }

    // Reports the supported fieldtypes.
    public function fieldtypes()
    {
        return array_keys($this->fieldtypes);
    }

    // Registers a new fieldtype.
    public function register_fieldtype($fieldtype, $implementing_class)
    {
        $this->store->register_fieldtype($fieldtype, $implementing_class);
        $this->fieldtypes[$fieldtype] = new $implementing_class();
    }

    // Saves the recordtypes supported by the repository.
    protected function load_recordtypes()
    {
        $this->recordtypes = $this->store->recordtypes();
    }

    // Reports the supported record types.
    public function recordtypes()
    {
        return array_keys($this->recordtypes);
    }

    // Registers a new recordtype.
    public function register_recordtype($recordtype, Array $fieldtypes)
    {
        $this->store->register_recordtype($recordtype, $fieldtypes);
        $this->recordtypes[$recordtype] = $fieldtypes;
    }

}