<?php

class FloatTest extends MuPHPUnitExtensions
{

    public function test_float()
    {
        $float = new \fijma\Mu\MockFloat();
        $expected = 3.14;
        $this->assertEquals($expected, $float->prepare($expected));
        $this->assertTrue($float->validate($expected));
        $this->assertFalse($float->validate(5));
        $this->assertEquals($expected, $float->convert($expected));
        $this->assertEquals('', $float->create('float'));
        $this->assertTrue($float->validate(null, true));
    }
}
