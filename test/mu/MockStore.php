<?php

use \fijma\Mu\Store;
/**
 * A datastore for testing the Mu api.
 */
class MockStore implements Store
{

    // Store the data
    private $store = [];

    // Store some relationships
    private $relationships = [];

    // Keep track of some ids
    private $id = 0;

    public function show_relationships()
    {
        return $this->relationships;
    }

    // If we have passed an exception, throw it, otherwise create a record.
    public function create($type, Array $data)
    {

        if (array_key_exists('exception', $data)) {
            throw new Exception($data['exception']);
        }

        $id = ++$this->id;
        $this->store[$id] = [  'id' => $id,
                             'type' => $type,
                          'version' => 'v1',
                          'deleted' => false,
                             'data' => $data];
        return $this->store[$id];
    }

    public function get($id)
    {
        return array_key_exists($id, $this->store) ? $this->store[$id] : null;
    }


    public function delete(Array $record)
    {
        $record['deleted'] = true;
        return $this->update($record);
    }

    public function undelete(Array $record)
    {
        $record['deleted'] = false;
        return $this->update($record);
    }

    public function update(Array $record)
    {
        if (array_key_exists($record['id'], $this->store)) {
            $version = $this->store[$record['id']]['version'];
            if ($record['version'] !== $version) {
                throw new \Exception('Version check failed.');
            }
            $record['version'] = 'v2';
            $this->store[$record['id']] = $record;
            return $record;
        } else {
            throw new \Exception('Record does not exist.');
        }
    }

    public function relate($relationship_type, $from, $to)
    {
        if (!array_key_exists($from, $this->store)) {
            throw new Exception("'From' record does not exist.");
        } elseif (!array_key_exists($to, $this->store)) {
            throw new Exception("'To' record does not exist.");
        }

        $relationship = [$relationship_type, $from, $to];
        if (!in_array($relationship, $this->relationships)) {
            $this->relationships[] = $relationship;
        }
    }

    public function unrelate($relationship_type, $from, $to)
    {
        if ($relationship_type === 'ExceptionTest') {
            throw new Exception('Unable to remove relationship.');
        } else {
            $key = array_search([$relationship_type, $from, $to], $this->relationships);
            if ($key !== false) {
                unset($this->relationships[$key]);
            }
        }
    }

    public function fieldtypes()
    {
        return ['boolean' => '\fijma\Mu\MockBoolean',
                'float' => '\fijma\Mu\MockFloat',
                'integer' => '\fijma\Mu\MockInteger',
                'string' => '\fijma\Mu\MockString'];
    }

    public function register_fieldtype($fieldtype, $implementing_class)
    {
        if($fieldtype === 'bugger') {
            throw new \Exception('Failed to register fieldtype ' . $fieldtype . '.');
        }
    }

    public function recordtypes()
    {
        return ['article' => ['title' => ['string', false],
                              'publishdate' => ['datetime', false],
                              'summary' => ['string', false],
                              'article' => ['string', false]]];
    }

    public function register_recordtype($recordtype, Array $fieldtypes)
    {
        if($recordtype === 'bugger') {
            throw new \Exception('Failed to register recordtype ' . $recordtype . '.');
        }

    }

    public function find($record_type, $params = [])
    {

    }

    public function related($record_id, $params = [])
    {

    }

    public function versions($record_id)
    {
        
    }

    public function deregister_fieldtype($fieldtype)
    {

    }

    public function deregister_recordtype($recordtype)
    {
        
    }

}
