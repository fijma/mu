<?php

class IntegerTest extends MuPHPUnitExtensions
{

    public function test_integer()
    {
        $integer = new \fijma\Mu\MockInteger();
        $expected = 3;
        $this->assertEquals($expected, $integer->prepare($expected));
        $this->assertTrue($integer->validate($expected));
        $this->assertFalse($integer->validate('five'));
        $this->assertEquals($expected, $integer->convert($expected));
        $this->assertNull($integer->create('integer'));
    }
}
