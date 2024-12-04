<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Workaround\Cleanup;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use Magento\TestFramework\Workaround\Cleanup\TestCaseProperties;

/**
 * Test class for \Magento\TestFramework\Workaround\Cleanup\TestCaseProperties.
 */
class TestCasePropertiesTest extends TestCase
{
    /**
     * @var array
     */
    protected $fixtureProperties = [
        'testPublic' => ['name' => 'testPublic', 'is_static' => false],
        '_testPrivate' => ['name' => '_testPrivate', 'is_static' => false],
        '_testPropertyBoolean' => ['name' => '_testPropertyBoolean', 'is_static' => false],
        '_testPropertyInteger' => ['name' => '_testPropertyInteger', 'is_static' => false],
        '_testPropertyFloat' => ['name' => '_testPropertyFloat', 'is_static' => false],
        '_testPropertyString' => ['name' => '_testPropertyString', 'is_static' => false],
        '_testPropertyArray' => ['name' => '_testPropertyArray', 'is_static' => false],
        'testPublicStatic' => ['name' => 'testPublicStatic', 'is_static' => true],
        '_testProtectedStatic' => ['name' => '_testProtectedStatic', 'is_static' => true],
        '_testPrivateStatic' => ['name' => '_testPrivateStatic', 'is_static' => true]
    ];

    /**
     * @return void
     */
    public function testEndTestSuiteDestruct(): void
    {
        $phpUnitTestSuite = TestSuite::empty('TestSuite');
        $phpUnitTestSuite->addTestFile(__DIR__ . '/TestCasePropertiesTest/DummyTestCase.php');
        // Because addTestFile() adds classes from file to tests array, use first testsuite
        /** @var TestSuite $testSuite */
        $testSuite = current($phpUnitTestSuite->tests());
        $testSuite->run();

        $reflectionClass = new \ReflectionClass($testSuite);
        $classProperties = $reflectionClass->getProperties();
        $fixturePropertiesNames = array_keys($this->fixtureProperties);

        foreach ($classProperties as $property) {
            if (in_array($property->getName(), $fixturePropertiesNames)) {
                $property->setAccessible(true);
                $value = $property->getValue($testSuite);
                $this->assertNotNull($value);
            }
        }
        $clearProperties = new TestCaseProperties();
        $clearProperties->endTestSuite($testSuite);

        foreach ($classProperties as $property) {
            if (in_array($property->getName(), $fixturePropertiesNames)) {
                $property->setAccessible(true);
                $value = $property->getValue($testSuite);
                $this->assertNull($value);
            }
        }
    }
}
