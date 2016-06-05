<?php

namespace fijma\Mu;

/**
 * Defines the api, performs data validation.
 */
class Mu
{

    // The api version
    private $version = '0.9.0';

    // The \fijma\Mu\Store instance used to access the repository.
    protected $store;

    // The field types supported by the repository.
    protected $fieldtypes = array();

    // The deregistered field types used by the repository (to access historical data).
    protected $deregistered_fieldtypes = array();

    // The record types supported by the repository.
    protected $recordtypes = array();

    // The deregistered record types used by the repository (to access historical data).
    protected $deregistered_recordtypes = array();

    public function __construct(\fijma\Mu\Store $store)
    {
        $this->store = $store;
        $this->load_fieldtypes();
        $this->load_recordtypes();
    }

    public function version()
    {
        return $this->version;
    }

    // Creates a new record (after validating the data). See \fijma\Mu\Store for documentation.
    public function create($type, Array $data)
    {
        $errors = $this->validate($type, $data);
        if($errors) throw new \Exception($errors);
        return $this->store->create($type, $data);
    }

    // Returns the record for the given id. See \fijma\Mu\Store for documentation.
    public function get($id)
    {
        return $this->store->get($id);
    }

    // Deletes the given record. See \fijma\Mu\Store for documentation.
    public function delete(Array $record)
    {
        $errors = $this->validate_record($record);
        if($errors) throw new \Exception($errors);
        return $this->store->delete($record);
    }

    // Undeletes the given record. See \fijma\Mu\Store for documentation.
    public function undelete(Array $record)
    {
        $errors = $this->validate_record($record);
        if($errors) throw new \Exception($errors);
        return $this->store->undelete($record);
    }

    // Updates the given record (after validating the data). See \fijma\Mu\Store for documentation.
    public function update(Array $record)
    {
        $errors = $this->validate_record($record);
        if($errors) throw new \Exception($errors);

        $errors = $this->validate($record['type'], $record['data']);
        if($errors) throw new \Exception($errors);

        return $this->store->update($record);
    }

    // Creates a relationship between two records. See \fijma\Mu\Store for documentation.
    public function relate($relationship_type, $from, $to)
    {
        $this->store->relate($relationship_type, $from, $to);
    }

    // Removes a relationship between two records. See \fijma\Mu\Store for documentation.
    public function unrelate($relationship_type, $from, $to)
    {
        $this->store->unrelate($relationship_type, $from, $to);
    }

    // Instantiates the field type objects into the fieldtypes array.
    protected function load_fieldtypes()
    {
        // active field types
        $fieldtypes = $this->store->fieldtypes();
        foreach ($fieldtypes as $fieldtype => $implementing_class) {
            $this->fieldtypes[$fieldtype] = new $implementing_class();
        }

        // deregistered field types
        $fieldtypes = $this->store->deregistered_fieldtypes();
        foreach ($fieldtypes as $fieldtype => $implementing_class) {
            $this->deregistered_fieldtypes[$fieldtype] = new $implementing_class();
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

        if(!in_array('fijma\Mu\FieldType', class_implements($implementing_class))) {
            throw new \Exception('Fieldtype implementing class must implement the \\fijma\\Mu\\FieldType interface.');
        }

        $this->store->register_fieldtype($fieldtype, $implementing_class);
        $this->fieldtypes[$fieldtype] = new $implementing_class();
    }

    // Deregisters a fieldtype. Returns true on success, false if the fieldtype was not registerd.
    public function deregister_fieldtype($fieldtype)
    {
        if (array_key_exists($fieldtype, $this->fieldtypes)) {
            $this->store->deregister_fieldtype($fieldtype);
            unset($this->fieldtypes[$fieldtype]);
            return true;
        } else {
            return false;
        }
    }

    // Reads the recordtypes supported by the repository.
    protected function load_recordtypes()
    {
        $this->recordtypes = $this->store->recordtypes();
        $this->deregistered_recordtypes = $this->store->deregistered_recordtypes();
    }

    // Reports the supported record types.
    public function recordtypes()
    {
        return array_keys($this->recordtypes);
    }

    // Registers a new recordtype.
    // Note that we accept a shorthand method of defining a field by the fieldtype alone if
    // it is mandatory. Otherwise, we need to supply the definition as an array.
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

        $fieldtype_list = array();
        $amended_fieldtypes = array();

        foreach($fieldtypes as $fieldname => $definition) {
            if (!is_string($fieldname)) {
                throw new \Exception('Field names must be strings.');
            }
            if (is_string($definition)) {
                $fieldtype_list[] = $definition;
                $amended_fieldtypes[$fieldname] = [$definition, false];
            } elseif (is_array($definition)) {
                if (count($definition) !== 2) {
                    throw new \Exception('Received invalid fieldtype definition array.');
                }
                if (!is_string($definition[0])) {
                    throw new \Exception('Received invalid fieldtype definition array.');
                }
                $fieldtype_list[] = $definition[0];
                if (!is_bool($definition[1])) {
                    throw new \Exception('Optional flag must be a boolean.');
                }
                $amended_fieldtypes[$fieldname] = $definition;
            } else {
                throw new \Exception('Received invalid fieldtype definition array.');
            }

        }

        $diff = array_diff($fieldtype_list, array_keys($this->fieldtypes));
        if(!empty($diff)) {
            $s = 'The following fieldtype';
            $s .= count($diff) > 1 ? 's are ' : ' is ';
            $s .= 'not registered: ';
            $s .= implode(', ', $diff);
            $s .= '.';
            throw new \Exception($s);
        }

        $this->store->register_recordtype($recordtype, $amended_fieldtypes);
        $this->recordtypes[$recordtype] = $amended_fieldtypes;
    }

    // Deregisters a recordtype. Returns true on success, false if the recordtype was not registerd.
    public function deregister_recordtype($recordtype)
    {
        if (array_key_exists($recordtype, $this->recordtypes)) {
            $this->store->deregister_recordtype($recordtype);
            unset($this->recordtypes[$recordtype]);
            return true;
        } else {
            return false;
        }
    }

    // Validates a record (ie as defined in \fijma\Mu\Store, and *not* the data itself).
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
            if(!$this->fieldtypes[$fieldtype[0]]->validate($data[$field], $fieldtype[1])) {
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

    /**
     * Returns all entries of the given $record_type. See \fijma\Mu\Store for documentation.
     */
    public function find($record_type, $params = [])
    {

    }

    /**
     * Validates arguments for the find function.
     * A valid record type is:
     *     0. A string
     *     1. Is a valid record type (can be deregistered)
     * A valid parameter array is:
     *     2. An array
     *     3. If it's not empty, it can only have the following keys: ['filter', 'order', 'limit', 'offset', 'deleted']
     *     4. For each item, perform the following validations:
     *         4.1. If filter, order, or deleted, confirm:
     *             4.1.1. The value is an array
     *             4.1.2. For each item in the array, confirm:
     *                 4.1.2.1. Each key in the array is a valid field for the given record type
     *                 4.1.2.2. If filter, each value in the array is valid for the field type
     *                 4.1.2.3. If order or deleted, each value in the array is a boolen
     *         4.2. If limit or offset, confirm the value is an integer
     */
    protected function validate_find_parameters($record_type, Array $params)
    {
        $errors = [];
        $record_type_definition = [];

        // 0. If it's not a string, we really can't go any further.
        if (!is_string($record_type)) {
            return 'Invalid record type. Expected string, received ' . gettype($record_type) . '.';
        }

        // 1. Similarly, if we can't find the record type, bomb out.
        if(array_key_exists($record_type, $this->recordtypes)) {
            $record_type_definition = $this->recordtypes[$record_type];
        } elseif(array_key_exists($record_type, $this->deregistered_fieldtypes)) {
            $record_type_definition = $this->deregistered_recordtypes[$record_type];
        } else {
            return 'Record type ' . $record_type . ' does not exist.';
        }

        // 2. If it's not an array, we haven't even started because of the type hint.
        // 3. First, if empty we're good to go.
        if (empty($params)) {
            return '';
        // 4. Otherwise, check that our keys are good.
        } else {
            $expected_keys = ['deleted', 'filter', 'limit', 'offset', 'order'];
            $diff = array_diff($expected_keys, array_keys($params));
            if (!empty($diff)) {
                $s = 'Invalid parameter';
                $s .= count($diff) > 1 ? 's: ' : ': ';
                $s .= implode(', ', $diff);
                $s .= '.';
                $errors[] = $s;
            }
        }

        // 5. Start the validation of the entries.
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'limit':
                case 'offset':
                    if (!is_integer($value)) {
                        $errors[] = 'Invalid value for ' . $key . ': expected integer, received ' . gettype($value) . '.';
                    }
                    break;
                case 'deleted':
                case 'filter':
                case 'order':
                    // 6. Check that the value is an array.
                    if (!is_array($value)) {
                        $errors[] = 'Invalid value for ' . $key . ': expected array, received ' . gettype($value) . '.';
                    }
                    // 7. Check that each key is a valid field for the record type.
                    // 8. If filter, each value is valid for the field type.
                    // 9. If order or deleted, each value is a boolen.
                     default:
                    // This should be unreachable
                    $errors[] = 'Invalid parameter: ' . $key . '.';
                    break;
            }
        }


    }

    /**
     * Returns all records which share a relationship with the record defined by $record_id.
     * See \fijma\Mu\Store for documentation.
     */
    public function related($record_id, $params = [])
    {

    }

    /**
     * Returns the version history for the given record.
     */
    public function versions($record_id)
    {
        
    }

}
