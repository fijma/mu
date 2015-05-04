<?php

use \Mu\Mu;
use \Mu\Store;

class MuTest extends MuPHPUnitExtensions
{
    
    private $mu;
    private $store;

    protected function setUp()
    {
        $this->store = new TestStore();
        $this->mu = new Mu($this->store);
    }

    public function test_mu_reports_the_store_its_using()
    {
        $this->assertEquals('TestStore', $this->mu->store());
    }

    public function test_mu_accepts_store_at_instanstiation()
    {
        $store = new TestStore();
        $mu = new Mu($store);
        $this->assertEquals('TestStore', $this->mu->store());
    }


    public function test_mu_creates_a_new_record()
    {
        $data = ['message' => "G'day cobber."];
        $record = $this->mu->create('record', $data);
        $this->assertInternalType('array', $record);
        $this->assertArrayHasKey('id', $record);
        $this->assertInternalType('integer', $record['id']);
        $this->assertArrayHasKey('type', $record);
        $this->assertInternalType('string', $record['type']);
        $this->assertEquals('record', $record['type']);
        $this->assertArrayHasKey('version', $record);
        $this->assertInternalType('string', $record['version']);
        $this->assertArrayHasKey('deleted', $record);
        $this->assertInternalType('boolean', $record['deleted']);
        $this->assertFalse($record['deleted']);
        $this->assertArrayHasKey('data', $record);
        $this->assertInternalType('array', $record['data']);
        $this->assertEquals("G'day cobber.", $record['data']['message']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Failed to create new record.
     */
    public function test_mu_throws_an_exception_when_create_fails()
    {
        $data = ['exception' => 'Failed to create new record.'];
        $this->mu->create('record', $data);
    }

    public function test_mu_gets_a_record()
    {
        $data = ['message' => "G'day cobber."];
        $this->mu->create('record', $data);
        $record = $this->mu->get(1);
        $this->assertInternalType('array', $record);
        $this->assertArrayHasKey('id', $record);
        $this->assertInternalType('integer', $record['id']);
        $this->assertArrayHasKey('type', $record);
        $this->assertInternalType('string', $record['type']);
        $this->assertEquals('record', $record['type']);
        $this->assertArrayHasKey('version', $record);
        $this->assertInternalType('string', $record['version']);
        $this->assertArrayHasKey('deleted', $record);
        $this->assertInternalType('boolean', $record['deleted']);
        $this->assertFalse($record['deleted']);
        $this->assertArrayHasKey('data', $record);
        $this->assertInternalType('array', $record['data']);
        $this->assertEquals("G'day cobber.", $record['data']['message']);
    }

    public function test_mu_returns_null_when_record_doesnt_exist()
    {
        $this->assertNull($this->mu->get(0));
    }

    public function test_mu_deletes_a_record()
    {
        $data = ['message' => "G'day cobber."];
        $record = $this->mu->create('record', $data);
        $version_before = $record['version'];
        $record = $this->mu->delete($record);
        $version_after = $record['version'];
        $this->assertNotEquals($version_before, $version_after);
        $this->assertEquals($version_after, $record['version']);
        $this->assertArrayHasKey('deleted', $record);
        $this->assertInternalType('boolean', $record['deleted']);
        $this->assertTrue($record['deleted']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Record does not exist.
     */
    public function test_mu_throws_an_exception_when_delete_fails()
    {
        $record = ['id' => 0];
        $this->mu->delete($record);
    }

    public function test_mu_updates_a_record()
    {
        $data = ['message' => "G'day cobber!"];
        $record = $this->mu->create('record', $data);
        $record_id = $record['id'];
        $version_before = $record['version'];
        $new_message = "How's it hangin'?";
        $record['data']['message'] = $new_message;
        $record = $this->mu->update($record);
        $version_after = $record['version'];
        $record = $this->mu->get($record_id);
        $this->assertNotEquals($version_before, $version_after);
        $this->assertEquals($version_after, $record['version']);
        $this->assertEquals($new_message, $record['data']['message']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Version check failed.
     */
    public function test_mu_checks_versions()
    {
        $record_one = $this->mu->create('record', ['message' => "G'day cobber!"]);
        $record_two = $this->mu->get(1);
        $record_one['data']['message'] = "How're they hangin'?";
        $record_one = $this->mu->update($record_one);
        $this->mu->update($record_two);
    }

    public function test_mu_relates_records()
    {
        $record_one = $this->mu->create('record', ['message' => "G'day cobber!"]);
        $record_two = $this->mu->create('record', ['message' => "How're they hangin'?"]);
        $this->mu->relate('link', $record_one['id'], $record_two['id']);
        $this->assertEquals($this->store->show_relationships(), [['link', 1, 2]]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage 'From' record does not exist.
     */
    public function test_mu_relate_throws_an_exception_if_the_from_record_does_not_exist()
    {
        $this->mu->relate('link', 1, 2);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage 'To' record does not exist.
     */
    public function test_mu_relate_throws_an_exception_if_the_to_record_does_not_exist()
    {
        $this->mu->create('record', ['message' => "G'day cobber!"]);
        $this->mu->relate('link', 1, 2);
    }

    public function test_mu_removes_relationships()
    {
        $record_one = $this->mu->create('record', ['message' => "G'day cobber!"]);
        $record_two = $this->mu->create('record', ['message' => "How're they hangin'?"]);
        $this->mu->relate('link', $record_one['id'], $record_two['id']);
        $this->assertEquals($this->store->show_relationships(), [['link', 1, 2]]);
        $this->mu->unrelate('link', $record_one['id'], $record_two['id']);
        $this->assertEmpty($this->store->show_relationships());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unable to remove relationship.
     */
    public function test_mu_throws_an_exception_if_it_cannot_remove_a_relationship()
    {
        $this->mu->unrelate('ExceptionTest', 1, 2);
    }

    public function test_mu_does_nothing_if_it_cant_find_the_relationship_to_remove()
    {
        $this->mu->unrelate('link', 1, 2);
    }

}