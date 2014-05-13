<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $_fixtureProperties = array(
        array('name' => 'testPublic', 'is_static' => false),
        array('name' => '_testPrivate', 'is_static' => false),
        array('name' => '_testPropertyBoolean', 'is_static' => false),
        array('name' => '_testPropertyInteger', 'is_static' => false),
        array('name' => '_testPropertyFloat', 'is_static' => false),
        array('name' => '_testPropertyString', 'is_static' => false),
        array('name' => '_testPropertyArray', 'is_static' => false),
        array('name' => '_testPropertyObject', 'is_static' => false),
        array('name' => 'testPublicStatic', 'is_static' => true),
        array('name' => '_testProtectedStatic', 'is_static' => true),
        array('name' => '_testPrivateStatic', 'is_static' => true)
    );

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

        $propertyObjectMock = $this->getMock('stdClass', array('__destruct'));
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
