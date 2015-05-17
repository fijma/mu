<?php

use \Mu\DateTime;

class DateTimeTest extends MuPHPUnitExtensions
{

    public function test_datetime()
    {
        $datetimezone = new \DateTimezone('Australia/Adelaide');
        $expected = new \DateTime(null, $datetimezone);
        $datetime = new DateTime();
        $this->assertEquals($expected, $datetime->prepare($expected));
        $this->assertTrue($datetime->validate($expected));
        $this->assertFalse($datetime->validate(5));
        $this->assertEquals($expected, $datetime->convert($expected));
    }
}
