<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Workaround\Cleanup\TestCaseProperties.
 */
namespace Magento\Test\Workaround\Cleanup;

class TestCasePropertiesTest extends \PHPUnit_Framework_TestCase
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
        $phpUnitTestSuite = new \PHPUnit_Framework_TestSuite();
        $phpUnitTestSuite->addTestFile(__DIR__ . '/TestCasePropertiesTest/DummyTestCase.php');
        // Because addTestFile() adds classes from file to tests array, use first testsuite
        /** @var $testSuite \PHPUnit_Framework_TestSuite */
        $testSuite = $phpUnitTestSuite->testAt(0);
        $testSuite->run();
        /** @var $testClass \Magento\Test\Workaround\Cleanup\TestCasePropertiesTest\DummyTestCase */
        $testClass = $testSuite->testAt(0);

        $propertyObjectMock = $this->getMock('stdClass', ['__destruct']);
        $propertyObjectMock->expects($this->atLeastOnce())->method('__destruct');
        $testClass->setPropertyObject($propertyObjectMock);

        foreach ($this->_fixtureProperties as $property) {
            if ($property['is_static']) {
                $this->assertAttributeNotEmpty($property['name'], get_class($testClass));
            } else {
                $this->assertAttributeNotEmpty($property['name'], $testClass);
            }
        }

        $clearProperties = new \Magento\TestFramework\Workaround\Cleanup\TestCaseProperties();
        $clearProperties->endTestSuite($testSuite);

        foreach ($this->_fixtureProperties as $property) {
            if ($property['is_static']) {
                $this->assertAttributeEmpty($property['name'], get_class($testClass));
            } else {
                $this->assertAttributeEmpty($property['name'], $testClass);
            }
        }
    }
}
