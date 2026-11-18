<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Automatic cleanup of test case's properties, it isn't needed to unset properties manually in tearDown() anymore
 */
namespace Magento\TestFramework\Workaround\Cleanup;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

class TestCaseProperties
{
    /**
     * @var array<int, TestCase>
     */
    private array $testCases = [];

    /**
     * Remember a test case for suite-end cleanup when the suite test list is empty.
     *
     * @param Test $test
     * @return void
     */
    public function startTest(Test $test): void
    {
        if ($test instanceof TestCase) {
            $this->testCases[spl_object_id($test)] = $test;
        }
    }

    /**
     * Clear test method properties after each test suite
     *
     * @param TestSuite $suite
     * @return void
     */
    public function endTestSuite(TestSuite $suite): void
    {
        $tests = $suite->tests();
        if ($tests === []) {
            $tests = array_values($this->testCases);
        }
        $this->testCases = [];

        foreach ($tests as $test) {
            $reflectionClass = new \ReflectionClass($test);
            $properties = $reflectionClass->getProperties();
            foreach ($properties as $property) {
                if ($property->isInitialized($test)) {
                    $value = $property->getValue($test);
                    if (is_object($value) && method_exists($value, '__destruct') &&
                        is_callable([$value, '__destruct'])) {
                        $value->__destruct();
                    }
                }

                if (is_array($property->getDefaultValue())) {
                    $property->setValue($test, []);
                } else {
                    $property->setValue($test, null);
                }
            }
        }
    }
}
