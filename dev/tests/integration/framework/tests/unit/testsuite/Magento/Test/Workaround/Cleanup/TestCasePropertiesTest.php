<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Workaround\Cleanup;

use Magento\TestFramework\Workaround\Cleanup\TestCaseProperties;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

/**
 * Test class for \Magento\TestFramework\Workaround\Cleanup\TestCaseProperties.
 */
class TestCasePropertiesTest extends TestCase
{
    /**
     * @var array
     */
    protected $fixtureProperties = [
        'testPublic' => ['name' => 'testPublic', 'is_static' => false, 'nullable' => true],
        '_testPrivate' => ['name' => '_testPrivate', 'is_static' => false, 'nullable' => true],
        '_testPropertyBoolean' => ['name' => '_testPropertyBoolean', 'is_static' => false, 'nullable' => true],
        '_testPropertyInteger' => ['name' => '_testPropertyInteger', 'is_static' => false, 'nullable' => true],
        '_testPropertyFloat' => ['name' => '_testPropertyFloat', 'is_static' => false, 'nullable' => true],
        '_testPropertyString' => ['name' => '_testPropertyString', 'is_static' => false, 'nullable' => true],
        '_testPropertyArray' => ['name' => '_testPropertyArray', 'is_static' => false, 'nullable' => true],
        '_testTypedNonNullable' => ['name' => '_testTypedNonNullable', 'is_static' => false, 'nullable' => false],
        '_testTypedNullable' => ['name' => '_testTypedNullable', 'is_static' => false, 'nullable' => true],
        'testPublicStatic' => ['name' => 'testPublicStatic', 'is_static' => true, 'nullable' => true],
        '_testProtectedStatic' => ['name' => '_testProtectedStatic', 'is_static' => true, 'nullable' => true],
        '_testPrivateStatic' => ['name' => '_testPrivateStatic', 'is_static' => true, 'nullable' => true]
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
                $isNullable = $this->fixtureProperties[$property->getName()]['nullable'];

                if ($isNullable) {
                    $this->assertNull($value);
                } else {
                    $this->assertNotNull($value);
                }
            }
        }
    }
}
