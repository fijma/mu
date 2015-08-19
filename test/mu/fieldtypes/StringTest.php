<?php

class StringTest extends MuPHPUnitExtensions
{

    public function test_string()
    {
        $string = new \Mu\MockString();
        $expected = 'string';
        $this->assertEquals($expected, $string->prepare($expected));
        $this->assertTrue($string->validate($expected));
        $this->assertFalse($string->validate(5));
        $this->assertEquals($expected, $string->convert($expected));
        $this->assertNull($string->create('string'));
    }
}
