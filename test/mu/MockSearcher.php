<?php

namespace fijma\Mu;

class MockSearcher implements \fijma\Mu\Searcher
{

    public function find($record_type, $params = []){}

    public function related($record_id, $params = []){}

    public function versions($record_id){}

}