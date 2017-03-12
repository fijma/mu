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
    public function create(string $type, array $data): array
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

    public function get($id): array
    {
        return array_key_exists($id, $this->store) ? $this->store[$id] : [];
    }


    public function delete(array $record): array
    {
        $record['deleted'] = true;
        return $this->update($record);
    }

    public function undelete(array $record): array
    {
        $record['deleted'] = false;
        return $this->update($record);
    }

    public function update(array $record): array
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

    public function relate(string $relationship_type, $from, $to): bool
    {
        if (!array_key_exists($from, $this->store)) {
            throw new Exception("'From' record does not exist.");
        } elseif (!array_key_exists($to, $this->store)) {
            throw new Exception("'To' record does not exist.");
        }

        $relationship = [$relationship_type, $from, $to];
        if (!in_array($relationship, $this->relationships)) {
            $this->relationships[] = $relationship;
            return true;
        }
        return false;
    }

    public function unrelate(string $relationship_type, $from, $to): bool
    {
        if ($relationship_type === 'ExceptionTest') {
            throw new Exception('Unable to remove relationship.');
        } else {
            $key = array_search([$relationship_type, $from, $to], $this->relationships);
            if ($key !== false) {
                unset($this->relationships[$key]);
                return true;
            }
            return false;
        }
    }

    public function fieldtypes(): array
    {
        return ['boolean' => '\fijma\Mu\MockBoolean',
                'float' => '\fijma\Mu\MockFloat',
                'string' => '\fijma\Mu\MockString'];
    }

    public function register_fieldtype(string $fieldtype, string $implementing_class)
    {
        if($fieldtype === 'bugger') {
            throw new \Exception('Failed to register fieldtype ' . $fieldtype . '.');
        }
    }

    public function recordtypes(): array
    {
        return ['article' => ['title' => ['string', false],
                              'publishdate' => ['datetime', false],
                              'summary' => ['string', false],
                              'article' => ['string', false]]];
    }

    public function register_recordtype(string $recordtype, array $fieldtypes)
    {
        if($recordtype === 'bugger') {
            throw new \Exception('Failed to register recordtype ' . $recordtype . '.');
        }

    }

    public function find(string $record_type, array $params = []): array
    {
        return [1 => [     'id' => 1,
                         'type' => 'article',
                      'version' => 'v1',
                      'deleted' => false,
                         'data' => []]
                ];

    }

    public function related($record_id, array $params = []): array
    {

    }

    public function versions($record_id): array
    {
        
    }

    public function deregister_fieldtype(string $fieldtype)
    {
        if($fieldtype === 'shite') {
            throw new Exception();
        }
    }

    public function deregister_recordtype(string $recordtype)
    {
        if($recordtype === 'shite') {
            throw new Exception();
        }
    }

    public function deregistered_fieldtypes(): array
    {
        return ['integer' => '\fijma\Mu\MockInteger'];

    }

    public function deregistered_recordtypes(): array
    {
        return ['listicle' => ['title' => ['string', false],
                              'publishdate' => ['datetime', false],
                              'summary' => ['string', false],
                              'article' => ['string', false]]];
    }

}
