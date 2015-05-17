<?php

use \Mu\Integer;

class IntegerTest extends MuPHPUnitExtensions
{

    public function test_integer()
    {
        $integer = new Integer();
        $expected = 3;
        $this->assertEquals($expected, $integer->prepare($expected));
        $this->assertTrue($integer->validate($expected));
        $this->assertFalse($integer->validate('five'));
        $this->assertEquals($expected, $integer->convert($expected));
    }
}
