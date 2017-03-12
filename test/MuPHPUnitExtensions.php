<?php

/**
 * Extend our extensions class for our test cases.
 *
 * As custom tests are required, add them here so they can be shared
 * by any future test cases.
 *
 * 1. Write a constraint object that performs the test (matches() is the worker function).
 * 2. Write a function for EntelechyPHPUnitExtension to return a constraint instance.
 * 3. Write a function for EntelechyPHPUnitExtension which calls assertThat() using the
 *    constraint returned by the previous function.
 *
 * Custom assertions
 *
 * assertObjectEquality($expected, $actual) asserts the equality of object instances.
 */

use PHPUnit\Framework\TestCase;

abstract class MuPHPUnitExtensions extends PHPUnit\Framework\TestCase {

    /**
     * Asserts equality of object instances
     */
    public static function assertIdentical($expected, $actual, $message = '') {
        self::assertThat($actual, self::getIdenticalityConstraint($expected), $message);
    }

    public static function getIdenticalityConstraint($expected) {
        return new MuConstraintIdenticality($expected);
    }

}

class MuConstraintIdenticality extends PHPUnit\Framework\Constraint\Constraint {
    
    private $expected;

    public function __construct($expected) {
        $this->expected = $expected;
    }

    protected function matches($actual) {
        return $actual === $this->expected;
    }

    public function toString() {
        return 'is identical to ' . print_r($this->expected, TRUE);
    }

}

?>