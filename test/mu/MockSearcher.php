<?php

namespace Mu;

class MockSearcher implements \Mu\Searcher
{

    public function find($record_type, $params = []){}

    public function related($record_id, $params = []){}

}