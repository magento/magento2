<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Workaround\Cleanup\TestCaseProperties.
 */
namespace Magento\Test\Workaround\Cleanup;

class TestCasePropertiesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    protected $_fixtureProperties = [
        ['name' => 'testPublic', 'is_static' => false],
        ['name' => '_testPrivate', 'is_static' => false],
        ['name' => '_testPropertyBoolean', 'is_static' => false],
        ['name' => '_testPropertyInteger', 'is_static' => false],
        ['name' => '_testPropertyFloat', 'is_static' => false],
        ['name' => '_testPropertyString', 'is_static' => false],
        ['name' => '_testPropertyArray', 'is_static' => false],
        ['name' => '_testPropertyObject', 'is_static' => false],
        ['name' => 'testPublicStatic', 'is_static' => true],
        ['name' => '_testProtectedStatic', 'is_static' => true],
        ['name' => '_testPrivateStatic', 'is_static' => true],
    ];

    public function testEndTestSuiteDestruct()
    {
        $phpUnitTestSuite = new \PHPUnit\Framework\TestSuite();
        $phpUnitTestSuite->addTestFile(__DIR__ . '/TestCasePropertiesTest/DummyTestCase.php');
        // Because addTestFile() adds classes from file to tests array, use first testsuite
        /** @var $testSuite \PHPUnit\Framework\TestSuite */
        $testSuite = $phpUnitTestSuite->testAt(0);
        $testSuite->run();
        /** @var $testClass \Magento\Test\Workaround\Cleanup\TestCasePropertiesTest\DummyTestCase */
        $testClass = $testSuite->testAt(0);

        $reflectionClass = new \ReflectionClass($testClass);
        $classProperties = $reflectionClass->getProperties();
        $fixturePropertiesNames = array_keys($this->_fixtureProperties);
        foreach ($classProperties as $property) {
            if (in_array($property->getName(), $fixturePropertiesNames)) {
                $property->setAccessible(true);
                $value = $property->getValue($testClass);
                $this->assertNotNull($value);
            }
        }

        $clearProperties = new \Magento\TestFramework\Workaround\Cleanup\TestCaseProperties();
        $clearProperties->endTestSuite($testSuite);

        foreach ($classProperties as $property) {
            if (in_array($property->getName(), $fixturePropertiesNames)) {
                $property->setAccessible(true);
                $value = $property->getValue($testClass);
                $this->assertNull($value);
            }
        }
    }
}
