<?php

use \Mu\Store;

/**
 * A datastore for testing the Mu api.
 */
class TestStore extends Store
{

    // Store the data
    private $store = [];

    // Keep track of some ids
    private $id = 0;

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

}