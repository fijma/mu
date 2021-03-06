<?php

class BooleanTest extends MuPHPUnitExtensions
{

    public function test_boolean()
    {
        $boolean = new \fijma\Mu\MockBoolean();
        $expected = true;
        $this->assertEquals($expected, $boolean->prepare($expected));
        $this->assertTrue($boolean->validate($expected));
        $this->assertFalse($boolean->validate(5));
        $this->assertEquals($expected, $boolean->convert($expected));
        $this->assertEquals('', $boolean->create('boolean'));
        $this->assertTrue($boolean->validate(null, true));
    }
}
