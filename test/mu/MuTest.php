<?php

use \fijma\Mu\Mu;
use \fijma\Mu\Store;
use \fijma\Mu\MockBoolean;
use \fijma\Mu\MockDateTime;
use \fijma\Mu\MockFloat;
use \fijma\Mu\MockInteger;
use \fijma\Mu\MockString;


class MuTest extends MuPHPUnitExtensions
{
    
    private $mu;
    private $store;

    protected function setUp()
    {
        $this->store = new MockStore();
        $this->mu = new MockMu($this->store);
    }

    public function test_mu_reports_the_store_its_using()
    {
        $this->assertEquals('MockStore', $this->mu->store());
    }

    public function test_mu_accepts_store_at_instanstiation()
    {
        $store = new MockStore();
        $mu = new MockMu($store);
        $this->assertEquals('MockStore', $this->mu->store());
    }


    public function test_mu_creates_a_new_record()
    {
        $this->mu->register_recordtype('record', ['message' => 'string']);
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
        $this->mu->register_recordtype('record', ['message' => 'string']);
        $data = ['message' => 'string', 'exception' => 'Failed to create new record.'];
        $this->mu->create('record', $data);
    }

    public function test_mu_gets_a_record()
    {
        $this->mu->register_recordtype('record', ['message' => 'string']);
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

    public function test_mu_returns_empty_array_when_record_doesnt_exist()
    {
        $this->assertEmpty($this->mu->get(0));
    }

    public function test_mu_deletes_a_record()
    {
        $this->mu->register_recordtype('record', ['message' => 'string']);
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
        $record = ['id' => 0, 'type' => '', 'version' => '', 'deleted' => false, 'data' => []];
        $this->mu->delete($record);
    }

    public function test_mu_undeletes_a_record()
    {
        $this->mu->register_recordtype('record', ['message' => 'string']);
        $data = ['message' => "G'day cobber."];
        $record = $this->mu->create('record', $data);
        $this->assertArrayHasKey('deleted', $record);
        $this->assertInternalType('boolean', $record['deleted']);
        $this->assertFalse($record['deleted']);
        $record = $this->mu->delete($record);
        $this->assertArrayHasKey('deleted', $record);
        $this->assertInternalType('boolean', $record['deleted']);
        $this->assertTrue($record['deleted']);
        $record = $this->mu->undelete($record);
        $this->assertArrayHasKey('deleted', $record);
        $this->assertInternalType('boolean', $record['deleted']);
        $this->assertFalse($record['deleted']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Record does not exist.
     */
    public function test_mu_throws_an_exception_when_undelete_fails()
    {
        $record = ['id' => 0, 'type' => '', 'version' => '', 'deleted' => true, 'data' => []];
        $this->mu->undelete($record);
    }

    public function test_mu_updates_a_record()
    {
        $this->mu->register_recordtype('record', ['message' => 'string']);
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
        $this->mu->register_recordtype('record', ['message' => 'string']);
        $record_one = $this->mu->create('record', ['message' => "G'day cobber!"]);
        $record_two = $this->mu->get(1);
        $record_one['data']['message'] = "How're they hangin'?";
        $record_one = $this->mu->update($record_one);
        $this->mu->update($record_two);
    }

    public function test_mu_relates_records()
    {
        $this->mu->register_recordtype('record', ['message' => 'string']);
        $record_one = $this->mu->create('record', ['message' => "G'day cobber!"]);
        $record_two = $this->mu->create('record', ['message' => "How're they hangin'?"]);
        $this->assertTrue($this->mu->relate('link', $record_one['id'], $record_two['id']));
        $this->assertEquals($this->store->show_relationships(), [['link', 1, 2]]);
    }

    public function test_mu_returns_false_if_you_relate_already_related_records()
    {
        $this->mu->register_recordtype('record', ['message' => 'string']);
        $record_one = $this->mu->create('record', ['message' => "G'day cobber!"]);
        $record_two = $this->mu->create('record', ['message' => "How're they hangin'?"]);
        $this->assertTrue($this->mu->relate('link', $record_one['id'], $record_two['id']));
        $this->assertEquals($this->store->show_relationships(), [['link', 1, 2]]);
        $this->assertFalse($this->mu->relate('link', $record_one['id'], $record_two['id']));
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
        $this->mu->register_recordtype('record', ['message' => 'string']);
        $this->mu->create('record', ['message' => "G'day cobber!"]);
        $this->mu->relate('link', 1, 2);
    }

    public function test_mu_removes_relationships_and_returns_true_when_it_does_so()
    {
        $this->mu->register_recordtype('record', ['message' => 'string']);
        $record_one = $this->mu->create('record', ['message' => "G'day cobber!"]);
        $record_two = $this->mu->create('record', ['message' => "How're they hangin'?"]);
        $this->assertTrue($this->mu->relate('link', $record_one['id'], $record_two['id']));
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

    public function test_mu_returns_false_if_it_cant_find_the_relationship_to_remove()
    {
        $this->assertFalse($this->mu->unrelate('link', 1, 2));
    }

    public function test_mu_reports_its_version()
    {
        $this->assertEquals('0.9.0', $this->mu->version());
    }

    public function test_mu_reports_the_fieldtypes_it_supports()
    {
        $expected = ['boolean', 'float', 'string'];
        $this->assertEquals($expected, $this->mu->fieldtypes());
    }

    public function test_mu_loads_deregistered_fieldtypes()
    {
        $expected = ['integer'];
        $df = $this->mu->show_me_your_deregistered_fieldtypes();
        $actual = array_keys($df);
        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf('\fijma\Mu\MockInteger', $df['integer']);
    }

    public function test_mu_can_register_fieldtypes()
    {
        $expected = ['boolean', 'datetime', 'float', 'string'];
        $this->mu->register_fieldtype('datetime', '\fijma\Mu\MockDateTime');
        $actual = $this->mu->fieldtypes();
        sort($actual);
        sort($expected);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Failed to register fieldtype bugger.
     */
    public function test_mu_gets_an_exception_when_registering_a_fieldtype_fails()
    {
        $this->mu->register_fieldtype('bugger', '\fijma\Mu\MockBoolean');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Fieldtype boolean is already registered.
     */
    public function test_mu_throws_an_exception_when_registering_an_existing_fieldtype()
    {
        $this->mu->register_fieldtype('boolean', '\fijma\Mu\MockBoolean');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Fieldtype implementing class 'hello' does not exist.
     */
    public function test_mu_throws_an_exception_if_implementing_fieldtype_class_does_not_exist()
    {
        $this->mu->register_fieldtype('hello', 'hello');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Fieldtype implementing class must implement the \fijma\Mu\FieldType interface.
     */
    public function test_mu_throws_an_exception_if_implementing_class_does_not_implement_fieldtype_interface()
    {
        $this->mu->register_fieldtype('hello', 'MockStore');
    }

    public function test_mu_returns_an_error_string_for_one_invalid_data_field()
    {
        $this->mu->register_fieldtype('datetime', '\fijma\Mu\MockDateTime');
        $expected = 'Received invalid data for the following field: publishdate(que).';
        $actual = $this->mu->test_validation('article', ['title' => 'test', 'publishdate' => 'que', 'summary' => 'test', 'article' => 'test']);
        $this->assertEquals($expected, $actual);
    }

    public function test_mu_returns_an_error_string_for_multiple_invalid_data_fields()
    {
        $this->mu->register_fieldtype('datetime', '\fijma\Mu\MockDateTime');
        $expected = 'Received invalid data for the following fields: title(1), publishdate(que), summary(1), article().';
        $actual = $this->mu->test_validation('article', ['title' => 1, 'publishdate' => 'que', 'summary' => true, 'article' => null]);
        $this->assertEquals($expected, $actual);
    }

    public function test_mu_reports_the_recordtypes_it_supports()
    {
        $this->assertEquals(['article'], $this->mu->recordtypes());
    }

    public function test_mu_loads_deregistered_recordtypes()
    {
        $expected = ['listicle' => ['title' => ['string', false],
                              'publishdate' => ['datetime', false],
                              'summary' => ['string', false],
                              'article' => ['string', false]]];
        $actual = $this->mu->show_me_your_deregistered_recordtypes();
        $this->assertEquals($expected, $actual);
    }

    public function test_mu_can_register_recordtypes()
    {
        $this->mu->register_recordtype('author', ['name' => 'string', 'email' => 'string']);
        $this->assertEquals(['article', 'author'], $this->mu->recordtypes());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Failed to register recordtype bugger.
     */
    public function test_mu_gets_an_exception_when_registering_a_recordtype_fails()
    {
        $this->mu->register_recordtype('bugger', ['name' => 'string']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Recordtype article is already registered.
     */
    public function test_mu_throws_an_exception_when_registering_an_existing_recordtype()
    {
        $this->mu->register_recordtype('article', ['name' => 'string']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The following fieldtype is not registered: text.
     */
    public function test_mu_throws_an_exception_when_registering_a_recordtype_which_has_an_unregistered_fieldtype()
    {
        $this->mu->register_recordtype('text', ['content' => 'text']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Fieldtype array cannot be empty.
     */
    public function test_mu_throw_an_exception_if_fieldtype_is_an_empty_array()
    {
        $this->mu->register_recordtype('test', []);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage The following fieldtype is not registered: blah.
    */
    public function test_mu_throws_an_exception_when_a_fieldtype_is_not_registered()
    {
        $this->mu->register_recordtype('test', ['blah' => 'blah']);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage Field names must be strings.
    */
    public function test_mu_throws_an_exception_if_field_names_are_not_strings()
    {
        $this->mu->register_recordtype('test', [1 => 'string']);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage The following fieldtypes are not registered: bleep, bloop.
    */
    public function test_mu_throws_an_exception_when_multiple_fieldtypes_are_not_registered()
    {
        $this->mu->register_recordtype('test', ['blip' => 'bleep', 'blup' => 'bloop']);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage Received invalid fieldtype definition array.
    */
    public function test_mu_throws_an_exception_when_invalid_fieldtype_in_recordtype_0()
    {
        $this->mu->register_recordtype('test', ['name' => ['invalid_array']]);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage Received invalid fieldtype definition array.
    */
    public function test_mu_throws_an_exception_when_invalid_fieldtype_in_recordtype_1()
    {
        $this->mu->register_recordtype('test', ['name' => [1, false]]);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage Optional flag must be a boolean.
    */
    public function test_mu_throws_an_exception_when_invalid_fieldtype_in_recordtype_2()
    {
        $this->mu->register_recordtype('test', ['name' => ['string', 1]]);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage Received invalid fieldtype definition array.
    */
    public function test_mu_throws_an_exception_when_invalid_fieldtype_in_recordtype_3()
    {
        $this->mu->register_recordtype('test', ['name' => 1]);
    }

    public function test_mu_can_register_recordtypes_with_optional_fieldtypes()
    {
        $this->mu->register_recordtype('author', ['name' => ['string', true]]);
        $this->assertEquals(['article', 'author'], $this->mu->recordtypes());

    }

    public function test_mu_returns_an_error_string_when_missing_one_record_field()
    {
        $expected = 'Invalid record - missing the following field: id.';
        $actual = $this->mu->test_record_validation(['type' => 'String', 'version' => '1', 'deleted' => false, 'data' => []]);
        $this->assertEquals($expected, $actual);
    }

    public function test_mu_returns_an_error_string_when_missing_multiple_record_fields()
    {
        $expected = 'Invalid record - missing the following fields: id, type.';
        $actual = $this->mu->test_record_validation(['version' => '1', 'deleted' => false, 'data' => []]);
        $this->assertEquals($expected, $actual);
    }

    public function test_mu_returns_an_error_string_for_one_invalid_record_field()
    {
        $expected = 'Invalid record - invalid data for the following field: id is not an integer.';
        $actual = $this->mu->test_record_validation(['id' => '1', 'type' => 'String', 'version' => '1', 'deleted' => false, 'data' => []]);
        $this->assertEquals($expected, $actual);
    }

    public function test_mu_returns_an_error_string_for_multiple_invalid_record_fields()
    {
        $expected = 'Invalid record - invalid data for the following fields: id is not an integer, type is not a string, version is not a string, deleted is not a boolean, data is not an array.';
        $actual = $this->mu->test_record_validation(['id' => '1', 'type' => 1, 'version' => 1, 'deleted' => 'String', 'data' => null]);
        $this->assertEquals($expected, $actual);
    }

    public function test_mu_returns_an_error_string_for_one_missing_data_field()
    {
        $datetimezone = new \DateTimezone('Australia/Adelaide');
        $expected = 'Missing the following field: title.';
        $actual = $this->mu->test_validation('article', ['publishdate' => new \DateTime(null, $datetimezone), 'summary' => 'summary', 'article' => 'article']);
        $this->assertEquals($expected, $actual);
    }

    public function test_mu_returns_an_error_string_for_multiple_missing_data_fields()
    {
        $expected = 'Missing the following fields: title, publishdate, summary, article.';
        $actual = $this->mu->test_validation('article', []);
        $this->assertEquals($expected, $actual);
    }

    public function test_mu_can_deregister_fieldtypes()
    {
        $expected = ['datetime', 'float', 'integer', 'string'];
        $this->assertTrue($this->mu->deregister_fieldtype('boolean'));
        $actual = $this->mu->fieldtypes();
        $this->assertEquals(sort($expected), sort($actual));
    }

    public function test_mu_returns_false_when_deregistering_a_non_existent_fieldtype()
    {
        $this->assertFalse($this->mu->deregister_fieldtype('bugger'));
    }

    public function test_mu_can_deregister_recordtypes()
    {
        $this->mu->register_recordtype('author', ['name' => 'string', 'email' => 'string']);
        $this->assertEquals(['article', 'author'], $this->mu->recordtypes());
        $this->assertTrue($this->mu->deregister_recordtype('article'));
        $this->assertEquals(['author'], $this->mu->recordtypes());
        $this->assertTrue(array_key_exists('article', $this->mu->show_me_your_deregistered_recordtypes()));
    }

    public function test_mu_returns_false_when_deregistering_a_non_existent_recordtype()
    {
        $this->assertFalse($this->mu->deregister_recordtype('author'));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage
     */
    public function test_store_throws_an_exception_when_deregister_fieldtype_fails()
    {
        $this->mu->register_fieldtype('shite', '\fijma\Mu\MockDateTime');
        $this->mu->deregister_fieldtype('shite');
    }

    /**
     * @expectedException Exception
     */
    public function test_store_throws_an_exception_when_deregister_recordtype_fails()
    {
        $this->mu->register_recordtype('shite', ['name' => 'string']);
        $this->mu->deregister_recordtype('shite');
    }

    public function test_mu_accepts_empty_search_parameters_for_find()
    {
        $results = $this->mu->find('article');
        // we're going to use our validation code to ensure the store is sending back a record.
        $this->assertEquals('', $this->mu->test_record_validation($results[1]));
    }

    public function test_mu_accepts_searches_for_deregistered_record_types()
    {
        $this->mu->deregister_recordtype('article');
        $results = $this->mu->find('article');
        // we're going to use our validation code to ensure the store is sending back a record.
        $this->assertEquals('', $this->mu->test_record_validation($results[1]));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Record type shisticle does not exist.
     */
    public function test_mu_rejects_non_existent_record_type_for_find()
    {
        $this->mu->find('shisticle');
    }


}
