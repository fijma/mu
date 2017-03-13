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
        $this->mu->register_record_type('record', ['message' => 'string']);
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
        $this->mu->register_record_type('record', ['message' => 'string']);
        $data = ['message' => 'string', 'exception' => 'Failed to create new record.'];
        $this->mu->create('record', $data);
    }

    public function test_mu_gets_a_record()
    {
        $this->mu->register_record_type('record', ['message' => 'string']);
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
        $this->mu->register_record_type('record', ['message' => 'string']);
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
        $this->mu->register_record_type('record', ['message' => 'string']);
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
        $this->mu->register_record_type('record', ['message' => 'string']);
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
        $this->mu->register_record_type('record', ['message' => 'string']);
        $record_one = $this->mu->create('record', ['message' => "G'day cobber!"]);
        $record_two = $this->mu->get(1);
        $record_one['data']['message'] = "How're they hangin'?";
        $record_one = $this->mu->update($record_one);
        $this->mu->update($record_two);
    }

    public function test_mu_relates_records()
    {
        $this->mu->register_record_type('record', ['message' => 'string']);
        $record_one = $this->mu->create('record', ['message' => "G'day cobber!"]);
        $record_two = $this->mu->create('record', ['message' => "How're they hangin'?"]);
        $this->assertTrue($this->mu->relate('link', $record_one['id'], $record_two['id']));
        $this->assertEquals($this->store->show_relationships(), [['link', 1, 2]]);
    }

    public function test_mu_returns_false_if_you_relate_already_related_records()
    {
        $this->mu->register_record_type('record', ['message' => 'string']);
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
        $this->mu->register_record_type('record', ['message' => 'string']);
        $this->mu->create('record', ['message' => "G'day cobber!"]);
        $this->mu->relate('link', 1, 2);
    }

    public function test_mu_removes_relationships_and_returns_true_when_it_does_so()
    {
        $this->mu->register_record_type('record', ['message' => 'string']);
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

    public function test_mu_reports_the_field_types_it_supports()
    {
        $expected = ['boolean', 'float', 'string'];
        $this->assertEquals($expected, $this->mu->field_types());
    }

    public function test_mu_loads_deregistered_field_types()
    {
        $expected = ['integer'];
        $df = $this->mu->show_me_your_deregistered_field_types();
        $actual = array_keys($df);
        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf('\fijma\Mu\MockInteger', $df['integer']);
    }

    public function test_mu_can_register_field_types()
    {
        $expected = ['boolean', 'datetime', 'float', 'string'];
        $this->mu->register_field_type('datetime', '\fijma\Mu\MockDateTime');
        $actual = $this->mu->field_types();
        sort($actual);
        sort($expected);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Failed to register field_type bugger.
     */
    public function test_mu_gets_an_exception_when_registering_a_field_type_fails()
    {
        $this->mu->register_field_type('bugger', '\fijma\Mu\MockBoolean');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Fieldtype boolean is already registered.
     */
    public function test_mu_throws_an_exception_when_registering_an_existing_field_type()
    {
        $this->mu->register_field_type('boolean', '\fijma\Mu\MockBoolean');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Fieldtype implementing class 'hello' does not exist.
     */
    public function test_mu_throws_an_exception_if_implementing_field_type_class_does_not_exist()
    {
        $this->mu->register_field_type('hello', 'hello');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Fieldtype implementing class must implement the \fijma\Mu\FieldType interface.
     */
    public function test_mu_throws_an_exception_if_implementing_class_does_not_implement_field_type_interface()
    {
        $this->mu->register_field_type('hello', 'MockStore');
    }

    public function test_mu_returns_an_error_string_for_one_invalid_data_field()
    {
        $this->mu->register_field_type('datetime', '\fijma\Mu\MockDateTime');
        $expected = 'Received invalid data for the following field: publishdate(que).';
        $actual = $this->mu->test_validation('article', ['title' => 'test', 'publishdate' => 'que', 'summary' => 'test', 'article' => 'test']);
        $this->assertEquals($expected, $actual);
    }

    public function test_mu_returns_an_error_string_for_multiple_invalid_data_fields()
    {
        $this->mu->register_field_type('datetime', '\fijma\Mu\MockDateTime');
        $expected = 'Received invalid data for the following fields: title(1), publishdate(que), summary(1), article().';
        $actual = $this->mu->test_validation('article', ['title' => 1, 'publishdate' => 'que', 'summary' => true, 'article' => null]);
        $this->assertEquals($expected, $actual);
    }

    public function test_mu_reports_the_record_types_it_supports()
    {
        $this->assertEquals(['article'], $this->mu->record_types());
    }

    public function test_mu_loads_deregistered_record_types()
    {
        $expected = ['listicle' => ['title' => ['string', false],
                              'publishdate' => ['datetime', false],
                              'summary' => ['string', false],
                              'article' => ['string', false]]];
        $actual = $this->mu->show_me_your_deregistered_record_types();
        $this->assertEquals($expected, $actual);
    }

    public function test_mu_can_register_record_types()
    {
        $this->mu->register_record_type('author', ['name' => 'string', 'email' => 'string']);
        $this->assertEquals(['article', 'author'], $this->mu->record_types());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Failed to register record_type bugger.
     */
    public function test_mu_gets_an_exception_when_registering_a_record_type_fails()
    {
        $this->mu->register_record_type('bugger', ['name' => 'string']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Recordtype article is already registered.
     */
    public function test_mu_throws_an_exception_when_registering_an_existing_record_type()
    {
        $this->mu->register_record_type('article', ['name' => 'string']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The following field_type is not registered: text.
     */
    public function test_mu_throws_an_exception_when_registering_a_record_type_which_has_an_unregistered_field_type()
    {
        $this->mu->register_record_type('text', ['content' => 'text']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Fieldtype array cannot be empty.
     */
    public function test_mu_throw_an_exception_if_field_type_is_an_empty_array()
    {
        $this->mu->register_record_type('test', []);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage The following field_type is not registered: blah.
    */
    public function test_mu_throws_an_exception_when_a_field_type_is_not_registered()
    {
        $this->mu->register_record_type('test', ['blah' => 'blah']);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage Field names must be strings.
    */
    public function test_mu_throws_an_exception_if_field_names_are_not_strings()
    {
        $this->mu->register_record_type('test', [1 => 'string']);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage The following field_types are not registered: bleep, bloop.
    */
    public function test_mu_throws_an_exception_when_multiple_field_types_are_not_registered()
    {
        $this->mu->register_record_type('test', ['blip' => 'bleep', 'blup' => 'bloop']);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage Received invalid field_type definition array.
    */
    public function test_mu_throws_an_exception_when_invalid_field_type_in_record_type_0()
    {
        $this->mu->register_record_type('test', ['name' => ['invalid_array']]);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage Received invalid field_type definition array.
    */
    public function test_mu_throws_an_exception_when_invalid_field_type_in_record_type_1()
    {
        $this->mu->register_record_type('test', ['name' => [1, false]]);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage Optional flag must be a boolean.
    */
    public function test_mu_throws_an_exception_when_invalid_field_type_in_record_type_2()
    {
        $this->mu->register_record_type('test', ['name' => ['string', 1]]);
    }

    /**
    * @expectedException Exception
    * @expectedExceptionMessage Received invalid field_type definition array.
    */
    public function test_mu_throws_an_exception_when_invalid_field_type_in_record_type_3()
    {
        $this->mu->register_record_type('test', ['name' => 1]);
    }

    public function test_mu_can_register_record_types_with_optional_field_types()
    {
        $this->mu->register_record_type('author', ['name' => ['string', true]]);
        $this->assertEquals(['article', 'author'], $this->mu->record_types());

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

    public function test_mu_can_deregister_field_types()
    {
        $expected = ['datetime', 'float', 'integer', 'string'];
        $this->assertTrue($this->mu->deregister_field_type('boolean'));
        $actual = $this->mu->field_types();
        $this->assertEquals(sort($expected), sort($actual));
    }

    public function test_mu_returns_false_when_deregistering_a_non_existent_field_type()
    {
        $this->assertFalse($this->mu->deregister_field_type('bugger'));
    }

    public function test_mu_can_deregister_record_types()
    {
        $this->mu->register_record_type('author', ['name' => 'string', 'email' => 'string']);
        $this->assertEquals(['article', 'author'], $this->mu->record_types());
        $this->assertTrue($this->mu->deregister_record_type('article'));
        $this->assertEquals(['author'], $this->mu->record_types());
        $this->assertTrue(array_key_exists('article', $this->mu->show_me_your_deregistered_record_types()));
    }

    public function test_mu_returns_false_when_deregistering_a_non_existent_record_type()
    {
        $this->assertFalse($this->mu->deregister_record_type('author'));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage
     */
    public function test_store_throws_an_exception_when_deregister_field_type_fails()
    {
        $this->mu->register_field_type('shite', '\fijma\Mu\MockDateTime');
        $this->mu->deregister_field_type('shite');
    }

    /**
     * @expectedException Exception
     */
    public function test_store_throws_an_exception_when_deregister_record_type_fails()
    {
        $this->mu->register_record_type('shite', ['name' => 'string']);
        $this->mu->deregister_record_type('shite');
    }

    public function test_mu_accepts_empty_search_parameters_for_find()
    {
        $results = $this->mu->find('article');
        // we're going to use our validation code to ensure the store is sending back a record.
        $this->assertEquals('', $this->mu->test_record_validation($results[1]));
    }

    public function test_mu_accepts_searches_for_deregistered_record_types()
    {
        $this->mu->deregister_record_type('article');
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

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Received invalid search parameters:
      - Received invalid option (shits).
     */
    public function test_mu_complains_about_a_single_invalid_find_parameter()
    {
        $this->mu->find('article', ['shits' => 0]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Received invalid search parameters:
      - Received invalid options (shits, giggles).
     */
    public function test_mu_complains_about_multiple_invalid_find_parameters()
    {
        $this->mu->find('article', ['shits' => 0, 'giggles' => 1]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Received invalid search parameters:
      - Invalid value for limit: expected integer, received string.
     */
    public function test_mu_complains_about_invalid_value_for_limit_find_parameter()
    {
        $this->mu->find('article', ['limit' => 'not a number']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Received invalid search parameters:
      - Invalid value for offset: expected integer, received boolean.
     */
    public function test_mu_complains_about_invalid_value_for_offset_find_parameter()
    {
        $this->mu->find('article', ['offset' => false]);
    }

   /**
    * @expectedException Exception
    * @expectedExceptionMessage Received invalid search parameters:
     - Invalid value for deleted: expected boolean, received integer.
    */
    public function test_mu_complains_about_invalid_value_for_deleted_find_parameter()
    {
        $this->mu->find('article', ['deleted' => 1]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Received invalid search parameters:
      - Invalid value for filter: expected array, received string.
     */
    public function test_mu_complains_about_invalid_value_for_filter_find_parameter()
    {
        $this->mu->find('article', ['filter' => 'not an array']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Received invalid search parameters:
      - Invalid value for order: expected array, received float.
     */
    public function test_mu_complains_about_invalid_value_for_order_find_parameter()
    {
        $this->mu->find('article', ['order' => 3.14]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Received invalid search parameters:
      - Invalid field for record_type article: shisticle.
     */
    public function test_mu_complains_about_invalid_fields_in_find_parameter()
    {
        $this->mu->find('article', ['filter' => ['shisticle' => true]]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Received invalid search parameters:
      - Invalid data for filter field: article.
     */
    public function test_mu_complains_about_invalid_filter_values_in_find_parameter()
    {
        $this->mu->find('article', ['filter' => ['article' => 2]]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Received invalid search parameters:
      - Invalid value for ordering article: expected boolean, received float.
     */
    public function test_mu_complains_about_invalid_order_values_in_find_parameter()
    {
        $this->mu->find('article', ['order' => ['article' => 4.2]]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Received invalid search parameters:
      - Invalid data for filter field: article.
      - Invalid value for deleted: expected boolean, received float.
      - Invalid value for ordering summary: expected boolean, received integer.
      - Invalid value for limit: expected integer, received string.
      - Invalid value for offset: expected integer, received boolean.
     */
    public function test_mu_complains_about_all_of_the_things_in_find_paramter()
    {
        $parameters = ['filter' => ['article' => 2, 'summary' => 'gah'],
                       'deleted' => 4.2,
                       'order' => ['article' => true, 'summary' => 3],
                       'limit' => 'not a number',
                       'offset' => true];
        $this->mu->find('article', $parameters);
    }

    public function test_mu_returns_an_empty_string_if_find_parameters_are_valid()
    {
        $parameters = ['filter' => ['article' => 'gah', 'summary' => 'gah'],
                       'deleted' => false,
                       'order' => ['article' => true, 'summary' => false],
                       'limit' => 42,
                       'offset' => 42];
        $this->assertEquals('', $this->mu->test_validate_find_parameters('article', $parameters));
    }
}
