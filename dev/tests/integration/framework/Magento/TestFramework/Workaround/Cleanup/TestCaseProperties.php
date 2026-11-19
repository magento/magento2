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
                if ($property->isStatic() || !$this->canResetProperty($property)) {
                    continue;
                }

                if ($property->isInitialized($test)) {
                    $value = $property->getValue($test);
                    if (is_object($value) && method_exists($value, '__destruct') &&
                        is_callable([$value, '__destruct'])) {
                        $value->__destruct();
                    }
                }

                $property->setValue($test, $this->resetValue($property));
            }
        }
    }

    /**
     * Static, readonly, and non-nullable typed properties cannot be cleared.
     *
     * Array properties are the exception: they are reset to an empty array.
     * Static properties are restored by StaticProperties.
     *
     * @param \ReflectionProperty $property
     * @return bool
     */
    private function canResetProperty(\ReflectionProperty $property): bool
    {
        if ($property->isStatic() || $property->isReadOnly()) {
            return false;
        }
        if (!$property->hasType()) {
            return true;
        }

        $type = $property->getType();
        if ($type->allowsNull()) {
            return true;
        }

        return $type instanceof \ReflectionNamedType && $type->getName() === 'array';
    }

    /**
     * Value written when a property is cleared.
     *
     * @param \ReflectionProperty $property
     * @return array|null
     */
    private function resetValue(\ReflectionProperty $property): array|null
    {
        if ($this->isRequiredArray($property)) {
            return [];
        }
        if ($property->hasDefaultValue() && is_array($property->getDefaultValue())) {
            return [];
        }

        return null;
    }

    /**
     * Typed array properties have no null state, so cleanup stores an empty array.
     *
     * @param \ReflectionProperty $property
     * @return bool
     */
    private function isRequiredArray(\ReflectionProperty $property): bool
    {
        if (!$property->hasType()) {
            return false;
        }

        $type = $property->getType();

        return $type instanceof \ReflectionNamedType
            && $type->getName() === 'array'
            && !$type->allowsNull();
    }
}
