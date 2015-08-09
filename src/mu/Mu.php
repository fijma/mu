<?php

namespace Mu;

/**
 * Defines the api, performs data validation.
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

    // Creates a new record (after validating the data). See \Mu\Store for documentation.
    public function create($type, Array $data)
    {
        $errors = $this->validate($type, $data);
        if($errors) throw new \Exception($errors);
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

    // Updates the given record (after validating the data). See \Mu\Store for documentation.
    public function update(Array $record)
    {
        $errors = $this->validate_record($record);
        if($errors) throw new \Exception($errors);

        $errors = $this->validate($record['type'], $record['data']);
        if($errors) throw new \Exception($errors);

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

    // Validates a record (ie as defined in \Mu\Store, and *not* the data itself).
    // Returns a message detailing the errors (empty string on success).
    protected function validate_record($record)
    {
        $keys = ['id', 'type', 'version', 'deleted', 'data'];

        $diff = array_diff($keys, array_keys($record));
        if(!empty($diff)) {
            $s = 'Invalid record - missing the following field';
            $s .= count($diff) > 1 ? 's: ' : ': ';
            $s .= implode(', ', $diff);
            $s .= '.';
            return $s;
        }

        $invalid_fields = [];
        if(!is_integer($record['id'])) {
            $invalid_fields[] = 'id is not an integer';
        }
        if (!is_string($record['type'])) {
            $invalid_fields[] = 'type is not a string';
        }
        if (!is_string($record['version'])) {
            $invalid_fields[] = 'version is not a string';
        }
        if(!is_bool($record['deleted'])) {
            $invalid_fields[] = 'deleted is not a boolean';
        }
        if(!is_array($record['data'])) {
            $invalid_fields[] = 'data is not an array';
        }

        if(!empty($invalid_fields)) {
            $s = 'Invalid record - invalid data for the following field';
            $s .=count($invalid_fields) > 1 ? 's: ' : ': ';
            $s .= implode(', ', $invalid_fields);
            $s .= '.';
            return $s;
        }

        return '';
    }

    // Validates a data array against the recordtype.
    // Returns a message detailing the errors (empty string on success).
    protected function validate($recordtype, Array $data)
    {
        $definition = $this->recordtypes[$recordtype];
        
        $diff = array_diff_key($definition, $data);
        if(!empty($diff)) {
            $s = 'Missing the following field';
            $s .= count($diff) > 1 ? 's: ' : ': ';
            $s .= implode(', ', array_keys($diff));
            $s .= '.';
            return $s;
        }

        $invalid_fields = array();
        foreach($definition as $field => $fieldtype) {
            if(!$this->fieldtypes[$fieldtype]->validate($data[$field])) {
                $invalid_fields[] = $field;
            }
        }
        if(!empty($invalid_fields)) {
            $s = 'Received invalid data for the following field';
            $s .= count($invalid_fields) > 1 ? 's: ' : ': ';
            $m = '';
            foreach($invalid_fields as $field) {
                $m .= mb_strlen($m) > 0 ? ', ' : '';
                $m .= $field . '(' . $data[$field] . ')';
            }
            $s .= $m . '.';
            return $s;
        }

        return '';

    }

}
