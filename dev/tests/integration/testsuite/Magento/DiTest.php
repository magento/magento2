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
 * @package     Magento_Di
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_DiTest extends PHPUnit_Framework_TestCase
{
    /**#@+
     * Test classes for basic instantiation
     */
    const TEST_CLASS           = 'Magento_Di_TestAsset_Basic';
    const TEST_CLASS_ALIAS     = 'Magento_Di_TestAsset_BasicAlias';
    const TEST_CLASS_INJECTION = 'Magento_Di_TestAsset_BasicInjection';
    /**#@-*/

    /**#@+
     * Test classes and interface to test preferences
     */
    const TEST_INTERFACE                = 'Magento_Di_TestAsset_Interface';
    const TEST_INTERFACE_IMPLEMENTATION = 'Magento_Di_TestAsset_InterfaceImplementation';
    const TEST_CLASS_WITH_INTERFACE     = 'Magento_Di_TestAsset_InterfaceInjection';
    /**#@-*/

    /**
     * @var Magento_ObjectManager
     */
    protected static $_objectManager;

    /**
     * List of classes with different number of arguments
     *
     * @var array
     */
    protected $_numerableClasses = array(
        0  => 'Magento_Di_TestAsset_ConstructorNoArguments',
        1  => 'Magento_Di_TestAsset_ConstructorOneArgument',
        2  => 'Magento_Di_TestAsset_ConstructorTwoArguments',
        3  => 'Magento_Di_TestAsset_ConstructorThreeArguments',
        4  => 'Magento_Di_TestAsset_ConstructorFourArguments',
        5  => 'Magento_Di_TestAsset_ConstructorFiveArguments',
        6  => 'Magento_Di_TestAsset_ConstructorSixArguments',
        7  => 'Magento_Di_TestAsset_ConstructorSevenArguments',
        8  => 'Magento_Di_TestAsset_ConstructorEightArguments',
        9  => 'Magento_Di_TestAsset_ConstructorNineArguments',
        10 => 'Magento_Di_TestAsset_ConstructorTenArguments',
    );

    /**
     * Names of properties
     *
     * @var array
     */
    protected $_numerableProperties = array(
        1  => '_one',
        2  => '_two',
        3  => '_three',
        4  => '_four',
        5  => '_five',
        6  => '_six',
        7  => '_seven',
        8  => '_eight',
        9  => '_nine',
        10 => '_ten',
    );

    public static function setUpBeforeClass()
    {
        $magentoDi = new Magento_Di_Zend();
        $magentoDi->instanceManager()->addTypePreference(self::TEST_INTERFACE, self::TEST_INTERFACE_IMPLEMENTATION);
        $magentoDi->instanceManager()->addAlias(self::TEST_CLASS_ALIAS, self::TEST_CLASS);
        self::$_objectManager = new Magento_ObjectManager_Zend(null, $magentoDi);
    }

    public static function tearDownAfterClass()
    {
        self::$_objectManager = null;
    }

    /**
     * Data provider for testNewInstance
     *
     * @return array
     */
    public function newInstanceDataProvider()
    {
        $data = array(
            'basic model' => array(
                '$actualClassName' => self::TEST_CLASS_INJECTION,
                '$properties'      => array('_object' => self::TEST_CLASS),
            ),
            'model with interface' => array(
                '$actualClassName' => self::TEST_CLASS_WITH_INTERFACE,
                '$properties'      => array('_object' => self::TEST_INTERFACE_IMPLEMENTATION),
            ),
            'model with alias' => array(
                '$actualClassName'   => self::TEST_CLASS_ALIAS,
                '$properties'        => array(),
                '$expectedClassName' => self::TEST_CLASS,
            ),
        );

        foreach ($this->_numerableClasses as $number => $className) {
            $properties = array();
            for ($i = 1; $i <= $number; $i++) {
                $propertyName = $this->_numerableProperties[$i];
                $properties[$propertyName] = self::TEST_CLASS;
            }
            $data[$number . ' arguments'] = array(
                '$actualClassName' => $className,
                '$properties'      => $properties,
            );
        }

        return $data;
    }

    /**
     * @param string $actualClassName
     * @param array $properties
     * @param string|null $expectedClassName
     *
     * @dataProvider newInstanceDataProvider
     */
    public function testNewInstance($actualClassName, array $properties = array(), $expectedClassName = null)
    {
        if (!$expectedClassName) {
            $expectedClassName = $actualClassName;
        }

        $testObject = self::$_objectManager->create($actualClassName);
        $this->assertInstanceOf($expectedClassName, $testObject);

        if ($properties) {
            foreach ($properties as $propertyName => $propertyClass) {
                $this->assertAttributeInstanceOf($propertyClass, $propertyName, $testObject);
            }
        }
    }
}
