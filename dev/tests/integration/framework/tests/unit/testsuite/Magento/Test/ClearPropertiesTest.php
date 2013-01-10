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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Magento_Test_ClearProperties.
 */
class Magento_Test_ClearPropertiesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $_properties = array(
        array(
            'name' => 'testPublic',
            'is_static' => false,
            'expectedValue' => 'public',
        ),
        array(
            'name' => '_testPrivate',
            'is_static' => false,
            'expectedValue' => 'private',
        ),
        array(
            'name' => '_testPropertyBoolean',
            'is_static' => false,
            'expectedValue' => true,
        ),
        array(
            'name' => '_testPropertyInteger',
            'is_static' => false,
            'expectedValue' => 10,
        ),
        array(
            'name' => '_testPropertyFloat',
            'is_static' => false,
            'expectedValue' => 1.97,
        ),
        array(
            'name' => '_testPropertyString',
            'is_static' => false,
            'expectedValue' => 'string',
        ),
        array(
            'name' => '_testPropertyArray',
            'is_static' => false,
            'expectedValue' => array('test', 20),
        ),
        array(
            'name' => 'testPublicStatic',
            'is_static' => true,
            'expectedValue' => 'static public',
        ),
        array(
            'name' => '_testProtectedStatic',
            'is_static' => true,
            'expectedValue' => 'static protected',
        ),
        array(
            'name' => '_testPrivateStatic',
            'is_static' => true,
            'expectedValue' => 'static private',
        ),
    );

    public function testEndTestSuiteDestruct()
    {
        $clearProperties = new Magento_Test_ClearProperties();
        $phpUnitTestSuite = new PHPUnit_Framework_TestSuite();
        $phpUnitTestSuite->addTestFile(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR
            . 'DummyTestCase.php'
        );
        // Because addTestFile() adds classes from file to tests array, use first testsuite
        /** @var $testSuite PHPUnit_Framework_TestSuite */
        $testSuite = $phpUnitTestSuite->testAt(0);
        $testSuite->run();
        $testClass = $testSuite->testAt(0);
        foreach ($this->_properties as $property) {
            if ($property['is_static']) {
                $this->assertAttributeEquals($property['expectedValue'], $property['name'], get_class($testClass));
            } else {
                $this->assertAttributeEquals($property['expectedValue'], $property['name'], $testClass);
            }
        }
        $clearProperties->endTestSuite($testSuite);
        $this->assertTrue(Magento_Test_ClearProperties_Stub::$isDestructCalled);
        foreach ($this->_properties as $property) {
            if ($property['is_static']) {
                $this->assertAttributeEmpty($property['name'], get_class($testClass));
            } else {
                $this->assertAttributeEmpty($property['name'], $testClass);
            }
        }
    }
}
