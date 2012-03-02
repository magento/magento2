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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Second part of Mage_Core_Model_Config testing:
 * - Mage factory behaviour is tested
 *
 * @group module:Mage_Core
 * @see Mage_Core_Model_ConfigTest
 */
class Mage_Core_Model_ConfigFactoryTest extends PHPUnit_Framework_TestCase
{
    protected static $_options = array();

    /** @var Mage_Core_Model_Config */
    protected $_model;

    public static function setUpBeforeClass()
    {
        self::$_options = Magento_Test_Bootstrap::getInstance()->getAppOptions();
    }

    public function setUp()
    {
        $this->_model = new Mage_Core_Model_Config;
        $this->_model->init(self::$_options);
    }

    /**
     * @dataProvider classNameRewriteDataProvider
     */
    public function testClassNameRewrite($originalClass, $expectedClass, $classNameGetter)
    {
        $this->_model->setNode("global/rewrites/$originalClass", $expectedClass);
        $this->assertEquals($expectedClass, $this->_model->$classNameGetter($originalClass));
    }

    public function classNameRewriteDataProvider()
    {
        return array(
            'block'          => array('My_Module_Block_Class', 'Another_Module_Block_Class', 'getBlockClassName'),
            'helper'         => array('My_Module_Helper_Data', 'Another_Module_Helper_Data', 'getHelperClassName'),
            'model'          => array('My_Module_Model_Class', 'Another_Module_Model_Class', 'getModelClassName'),
            'resource model' => array(
                'My_Module_Model_Resource_Collection',
                'Another_Module_Model_Resource_Collection_New',
                'getResourceModelClassName'
            ),
        );
    }

    public function testGetBlockClassName()
    {
        $this->assertEquals('Mage_Core_Block_Template', $this->_model->getBlockClassName('Mage_Core_Block_Template'));
    }

    public function testGetHelperClassName()
    {
        $this->assertEquals('Mage_Core_Helper_Http', $this->_model->getHelperClassName('Mage_Core_Helper_Http'));
    }

    public function testGetResourceHelper()
    {
        $this->assertInstanceOf(
            'Mage_Core_Model_Resource_Helper_Abstract', $this->_model->getResourceHelper('Mage_Core')
        );
    }

    public function testGetModelClassName()
    {
        $this->assertEquals('Mage_Core_Model_Config', $this->_model->getModelClassName('Mage_Core_Model_Config'));
    }

    public function testGetModelInstance()
    {
        $this->assertInstanceOf('Mage_Core_Model_Config', $this->_model->getModelInstance('Mage_Core_Model_Config'));
    }

    public function testGetResourceModelClassName()
    {
        $this->assertEquals(
            'Mage_Core_Model_Resource_Config',
            $this->_model->getResourceModelClassName('Mage_Core_Model_Resource_Config')
        );
    }

    public function testGetResourceModelInstance()
    {
        $this->assertInstanceOf(
            'Mage_Core_Model_Resource_Config',
            $this->_model->getResourceModelInstance('Mage_Core_Model_Resource_Config')
        );
    }
}
