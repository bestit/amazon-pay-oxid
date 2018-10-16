<?php

use SebastianBergmann\Exporter\Exporter;

// Definition of PHPUnitContraintParent class for different phpunit versions
if(class_exists('PHPUnit_Framework_Constraint')) {
    abstract class PHPUnitContraintParent extends PHPUnit_Framework_Constraint {}
} else {
    abstract class PHPUnitContraintParent extends PHPUnit\Framework\Constraint\Constraint {}
}

/**
 * Helper class to access StringMatchIgnoreWhitespace class for OXID versions without changing of unit test files
 *
 * Original code from: https://github.com/etsy/phpunit-extensions/blob/master/src/PHPUnit/Extensions/Constraint/StringMatchIgnoreWhitespace.php
 *
 * @author Benjamin Gutmann <benjamin.gutmann@bestit-online.de>
 */
class MatchIgnoreWhitespace extends PHPUnitContraintParent
{
    private $expected;
    protected $exporter;

    public function __construct($expected)
    {
        $this->expected = $expected;
        $this->exporter = new Exporter();
    }

    protected function matches($actual)
    {
        return $this->normalize($this->expected) == $this->normalize($actual);
    }

    private function normalize($string)
    {
        /**
         * the extra replace is because exporter started putting
         * the identifiers next to the array name
         * like so:
         * Array &0 (
         *  Array &1 (
         *  ....
         * which we previously didn't expect
         */
        return preg_replace('#\&. #','', implode(' ', preg_split('/\s+/', trim($string))));
    }

    public function toString()
    {
        return sprintf(
            'equals ignoring whitespace %s',
            $this->exporter->export($this->normalize($this->expected)));
    }
}
