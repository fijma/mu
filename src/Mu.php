<?php

namespace fijma\Mu;

/**
 * Defines the api, performs data validation.
 */
class Mu
{

    // The api version
    private $version = '0.0.0';

    // The \fijma\Mu\Store instance used to access the repository.
    protected $store;

    // The field types supported by the repository.
    protected $field_types = array();

    // The deregistered field types used by the repository (to access historical data).
    protected $deregistered_field_types = array();

    // The record types supported by the repository.
    protected $record_types = array();

    // The deregistered record types used by the repository (to access historical data).
    protected $deregistered_record_types = array();

    public function __construct(\fijma\Mu\Store $store)
    {
        $this->store = $store;
        $this->load_field_types();
        $this->load_record_types();
    }

    public function version(): string
    {
        return $this->version;
    }

    // Creates a new record (after validating the data). See \fijma\Mu\Store for documentation.
    public function create(string $type, array $data): array
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
    public function delete(array $record): array
    {
        $errors = $this->validate_record($record);
        if($errors) throw new \Exception($errors);
        return $this->store->delete($record);
    }

    // Undeletes the given record. See \fijma\Mu\Store for documentation.
    public function undelete(array $record): array
    {
        $errors = $this->validate_record($record);
        if($errors) throw new \Exception($errors);
        return $this->store->undelete($record);
    }

    // Updates the given record (after validating the data). See \fijma\Mu\Store for documentation.
    public function update(array $record): array
    {
        $errors = $this->validate_record($record);
        if($errors) throw new \Exception($errors);

        $errors = $this->validate($record['type'], $record['data']);
        if($errors) throw new \Exception($errors);

        return $this->store->update($record);
    }

    // Creates a relationship between two records. See \fijma\Mu\Store for documentation.
    public function relate(string $relationship_type, $from, $to): bool
    {
        return $this->store->relate($relationship_type, $from, $to);
    }

    // Removes a relationship between two records. See \fijma\Mu\Store for documentation.
    public function unrelate(string $relationship_type, $from, $to): bool
    {
        return $this->store->unrelate($relationship_type, $from, $to);
    }

    // Instantiates the field type objects into the field_types array.
    protected function load_field_types()
    {
        // active field types
        $field_types = $this->store->field_types();
        foreach ($field_types as $field_type => $implementing_class) {
            $this->field_types[$field_type] = new $implementing_class();
        }

        // deregistered field types
        $field_types = $this->store->deregistered_field_types();
        foreach ($field_types as $field_type => $implementing_class) {
            $this->deregistered_field_types[$field_type] = new $implementing_class();
        }
    }

    // Reports the supported field_types.
    public function field_types(): array
    {
        return array_keys($this->field_types);
    }

    // Registers a new field_type.
    public function register_field_type(string $field_type, string $implementing_class)
    {
        if(in_array($field_type, $this->field_types())) {
            throw new \Exception('Fieldtype ' . $field_type . ' is already registered.');
        }

        if (!class_exists($implementing_class)) {
            throw new \Exception('Fieldtype implementing class \'' . $implementing_class . '\' does not exist.');
        }

        if(!in_array('fijma\Mu\FieldType', class_implements($implementing_class))) {
            throw new \Exception('Fieldtype implementing class must implement the \\fijma\\Mu\\FieldType interface.');
        }

        $this->store->register_field_type($field_type, $implementing_class);
        $this->field_types[$field_type] = new $implementing_class();
    }

    // Deregisters a field_type. Returns true on success, false if the field_type was not registerd.
    public function deregister_field_type(string $field_type)
    {
        if (array_key_exists($field_type, $this->field_types)) {
            $this->store->deregister_field_type($field_type);
            unset($this->field_types[$field_type]);
            return true;
        } else {
            return false;
        }
    }

    // Reads the record_types supported by the repository.
    protected function load_record_types()
    {
        $this->record_types = $this->store->record_types();
        $this->deregistered_record_types = $this->store->deregistered_record_types();
    }

    // Reports the supported record types.
    public function record_types(): array
    {
        return array_keys($this->record_types);
    }

    // Registers a new record_type.
    // Note that we accept a shorthand method of defining a field by the field_type alone if
    // it is mandatory. Otherwise, we need to supply the definition as an array.
    public function register_record_type(string $record_type, array $field_types)
    {
        if(array_key_exists($record_type, $this->record_types)) {
            throw new \Exception('Recordtype ' . $record_type . ' is already registered.');
        }

        if(empty($field_types)) {
            throw new \Exception('Fieldtype array cannot be empty.');
        }

        $field_type_list = array();
        $amended_field_types = array();

        foreach($field_types as $fieldname => $definition) {
            if (!is_string($fieldname)) {
                throw new \Exception('Field names must be strings.');
            }
            if (is_string($definition)) {
                $field_type_list[] = $definition;
                $amended_field_types[$fieldname] = [$definition, false];
            } elseif (is_array($definition)) {
                if (count($definition) !== 2) {
                    throw new \Exception('Received invalid field_type definition array.');
                }
                if (!is_string($definition[0])) {
                    throw new \Exception('Received invalid field_type definition array.');
                }
                $field_type_list[] = $definition[0];
                if (!is_bool($definition[1])) {
                    throw new \Exception('Optional flag must be a boolean.');
                }
                $amended_field_types[$fieldname] = $definition;
            } else {
                throw new \Exception('Received invalid field_type definition array.');
            }

        }

        $diff = array_diff($field_type_list, array_keys($this->field_types));
        if(!empty($diff)) {
            $s = 'The following field_type';
            $s .= count($diff) > 1 ? 's are ' : ' is ';
            $s .= 'not registered: ';
            $s .= implode(', ', $diff);
            $s .= '.';
            throw new \Exception($s);
        }

        $this->store->register_record_type($record_type, $amended_field_types);
        $this->record_types[$record_type] = $amended_field_types;
    }

    // Deregisters a record_type. Returns true on success, false if the record_type was not registerd.
    public function deregister_record_type(string $record_type)
    {
        if (array_key_exists($record_type, $this->record_types)) {
            $this->deregistered_record_types[$record_type] = $this->record_types[$record_type];
            $this->store->deregister_record_type($record_type);
            unset($this->record_types[$record_type]);
            return true;
        } else {
            return false;
        }
    }

    // Validates a record (ie as defined in \fijma\Mu\Store, and *not* the data itself).
    // Returns a message detailing the errors (empty string on success).
    protected function validate_record(array $record): string
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

    // Validates a data array against the record_type.
    // Returns a message detailing the errors (empty string on success).
    protected function validate(string $record_type, array $data): string
    {
        $definition = $this->record_types[$record_type];
        
        $diff = array_diff_key($definition, $data);
        if(!empty($diff)) {
            $s = 'Missing the following field';
            $s .= count($diff) > 1 ? 's: ' : ': ';
            $s .= implode(', ', array_keys($diff));
            $s .= '.';
            return $s;
        }

        $invalid_fields = array();
        foreach($definition as $field => $field_type) {
            if(!$this->field_types[$field_type[0]]->validate($data[$field], $field_type[1])) {
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
    public function find(string $record_type, array $params = []): array
    {
        $errors = $this->validate_find_parameters($record_type, $params);
        if ($errors) throw new \Exception($errors);
        return $this->store->find($record_type, $params);
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
     *         4.1. If filter or order, confirm:
     *             4.1.1. The value is an array
     *             4.1.2. For each item in the array, confirm:
     *                 4.1.2.1. Each key in the array is a valid field for the given record type
     *                 4.1.2.2. If filter, each value in the array is valid for the field type
     *                 4.1.2.3. If order, each value in the array is a boolean
     *         4.2. If limit or offset, confirm the value is an integer
     *         4.3. If delete, confirm the value is a boolean
     */
    protected function validate_find_parameters(string $record_type, array $params): string
    {
        $errors = [];
        $record_type_definition = [];

        // 0. If it's not a string, we haven't even started because of the type declaration.
        // 1. Similarly, if we can't find the record type, bomb out.
        if(array_key_exists($record_type, $this->record_types)) {
            $record_type_definition = $this->record_types[$record_type];
        } elseif(array_key_exists($record_type, $this->deregistered_record_types)) {
            $record_type_definition = $this->deregistered_record_types[$record_type];
        } else {
            return 'Record type ' . $record_type . ' does not exist.';
        }

        // 2. If it's not an array, we haven't even started because of the type declaration.
        // 3. First, if empty we're good to go.
        if (empty($params)) {
            return '';
        // 4. Otherwise, check that our keys are good.
        } else {
            $expected_keys = ['deleted', 'filter', 'limit', 'offset', 'order'];
            $diff = array_diff(array_keys($params), $expected_keys);
            if (!empty($diff)) {
                $s = 'Received invalid option';
                $s .= count($diff) > 1 ? 's (' : ' (';
                $s .= implode(', ', $diff);
                $s .= ').';
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
                    if (!is_bool($value)) {
                        $errors[] = 'Invalid value for ' . $key . ': expected boolean, received ' . gettype($value) . '.';
                    }
                    break;
                case 'filter':
                case 'order':
                    // 6. Check that the value is an array.
                    if (!is_array($value)) {
                        $errors[] = 'Invalid value for ' . $key . ': expected array, received ' . gettype($value) . '.';
                        break;
                    }
                    // 7. Check that each key is a valid field for the record type.
                    foreach($value as $field => $value) {
                        if(!array_key_exists($field, $record_type_definition)) {
                            $errors[] = 'Invalid field for record_type ' . $record_type . ': ' . $field . '.';
                            continue;
                        }
                        // 8. If filter, each value is valid for the field type.
                        if ($key === 'filter') {
                            if(!$this->field_types[$record_type_definition[$field][0]]->validate($value, $record_type_definition[$field][0])) {
                                $errors[] = 'Invalid data for filter field: ' . $field . '.';
                            }
                        // 9. If order, each value is a boolen.
                        } else {
                            if(!is_bool($value)) {
                                $errors[] = 'Invalid value for ordering ' . $field . ': expected boolean, received ' . gettype($value) . '.';
                            }
                        }
                    }
                    break;
                default:
                    //do nothing, we've caught this already
            }
        }
        if(empty($errors)) {
            return '';
        } else {
            // make the return string here.
            $errmsg = "Received invalid search parameters: ";
            return $errmsg . implode("\n - ", $errors);
        }
    }

    /**
     * Returns all records which share a relationship with the record identified by $record_id.
     * See \fijma\Mu\Store for documentation.
     */
    public function related($record_id, array $params = []): array
    {
        $errors = $this->validate_related_parameters($params);
        if ($errors) throw new \Exception($errors);
        return $this->store->related($record_id, $params);
    }

    /**
     * Validates arguments for the related function.
     */
    protected function validate_related_parameters(array $params): string
    {
        $errors = [];
        $record_type = '';

        // First, if empty we're good to go.
        if (empty($params)) {
            return '';
        // Otherwise, check that our keys are good.
        } else {
            $expected_keys = ['relationship_type', 'direction', 'record_type', 'filter', 'deleted'];
            $diff = array_diff(array_keys($params), $expected_keys);
            if (!empty($diff)) {
                $s = 'Received invalid option';
                $s .= count($diff) > 1 ? 's (' : ' (';
                $s .= implode(', ', $diff);
                $s .= ').';
                $errors[] = $s;
            }
        }

        // Start the validation of the entries.
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'relationship_type':
                case 'direction':
                case 'record_type':
                    if (!is_string($value)) {
                        $errors[] = 'Invalid value for ' . $key . ': expected string, received ' . gettype($value) . '.';
                        break;
                    }

                    if ($key === 'direction' && !($value === 'to' || $value === 'from')) {
                        $errors[] = 'Invalid value for direction: expected "to" or "from", received ' . $value . '.';
                        break;
                    }
                case 'deleted':
                    if (!is_bool($value)) {
                        $errors[] = 'Invalid value for ' . $key . ': expected boolean, received ' . gettype($value) . '.';
                    }
                    break;
                case 'filter':
                    $record_type_definition = [];            
                    // If record_type hasn't been defined, we shouldn't have this option.
                    if (!array_key_exists('record_type', $params)) {
                        $errors[] = 'Cannot define filters without a record_type.';
                        break;
                    } else {
                        if(array_key_exists($record_type, $this->record_types)) {
                            $record_type_definition = $this->record_types[$record_type];
                        } elseif(array_key_exists($record_type, $this->deregistered_record_types)) {
                            $record_type_definition = $this->deregistered_record_types[$record_type];
                        } else {
                            $errors[] = 'Record type ' . $record_type . ' does not exist.';
                            break;
                        }
                        // Check that each key is a valid field for the record type.
                        foreach($value as $field => $value) {
                            if(!array_key_exists($field, $record_type_definition)) {
                                $errors[] = 'Invalid field for record_type ' . $record_type . ': ' . $field . '.';
                                continue;
                            }
                            if(!$this->field_types[$record_type_definition[$field][0]]->validate($value, $record_type_definition[$field][0])) {
                                $errors[] = 'Invalid data for filter field: ' . $field . '.';
                            }
                        }
                    }
                default:
                    //do nothing, we've caught this already
            }
        }

        if(empty($errors)) {
            return '';
        } else {
            // make the return string here.
            $errmsg = "Received invalid search parameters: ";
            return $errmsg . implode("\n - ", $errors);
        }
    }

    /**
     * Returns the version history for the given record.
     */
    public function versions($record_id): array
    {
        
    }

}
