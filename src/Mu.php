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

    // The search providers supported by the repository.
    protected $searchers = array();

    public function __construct(\Mu\Store $store)
    {
        $this->store = $store;
        $this->load_fieldtypes();
        $this->load_recordtypes();
        $this->load_searchers();
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
        if (!is_string($fieldtype)) {
            throw new \Exception('Fieldtype name must be a string.');
        }

        if(in_array($fieldtype, $this->fieldtypes())) {
            throw new \Exception('Fieldtype ' . $fieldtype . ' is already registered.');
        }

        if (!class_exists($implementing_class)) {
            throw new \Exception('Fieldtype implementing class \'' . $implementing_class . '\' does not exist.');
        }

        if(!in_array('Mu\FieldType', class_implements($implementing_class))) {
            throw new \Exception('Fieldtype implementing class must implement the \\Mu\\FieldType interface.');
        }

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
        if(!is_string($recordtype)) {
            throw new \Exception('Recordtype name must be a string.');
        }

        if(array_key_exists($recordtype, $this->recordtypes)) {
            throw new \Exception('Recordtype ' . $recordtype . ' is already registered.');
        }

        if(empty($fieldtypes)) {
            throw new \Exception('Fieldtype array cannot be empty.');
        }

        $diff = array_diff($fieldtypes, array_keys($this->fieldtypes));
        if(!empty($diff)) {
            $s = 'The following fieldtype';
            $s .= count($diff) > 1 ? 's are ' : ' is ';
            $s .= 'not registered: ';
            $s .= implode(', ', $diff);
            $s .= '.';
            throw new \Exception($s);
        }

        foreach(array_keys($fieldtypes) as $fieldname) {
            if (!is_string($fieldname)) {
                throw new \Exception('Field names must be strings.');
            }
        }

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

    // Instantiates the search provider objects into the fieldtypes array.
    protected function load_searchers()
    {
        $searchers = $this->store->searchers();
        foreach ($searchers as $searcher => $implementing_class) {
            $this->searchers[$searcher] = new $implementing_class();
        }
    }

    // Reports the registered search providers.
    public function searchers()
    {
        return array_keys($this->searchers);
    }

    // Registers a new search provider.
    public function register_searcher($searcher, $implementing_class)
    {
        if (in_array($searcher, $this->searchers())) {
            throw new \Exception('Search provider ' . $searcher . ' is already registered.');
        }

        if (!is_string($searcher)) {
            throw new \Exception('Search provider name must be a string.');
        }

        if (!class_exists($implementing_class)) {
            throw new \Exception('Search provider implementing class \'' . $implementing_class . '\' does not exist.');
        }

        if(!in_array('Mu\Searcher', class_implements($implementing_class))) {
            throw new \Exception('Search provider implementing class must implement the \\Mu\\Searcher interface.');
        }

        $this->store->register_searcher($searcher, $implementing_class);
        $this->searchers[$searcher] = new $implementing_class();
    }

    // Returns the requested search provider
    public function searcher($searcher)
    {
        if(!in_array($searcher, $this->searchers())) {
            throw new \Exception('Search provider ' . $searcher . ' has not been registered.');
        }

        return $this->searchers[$searcher];
    }

}
