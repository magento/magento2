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
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Config_LoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Config_Loader
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configDataFactory;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configCollection;

    protected function setUp()
    {
        $this->_configDataFactory = $this->getMock(
            'Mage_Core_Model_Config_Data_Factory', array('create', 'getCollection'), array(), '', false
        );
        $this->_model = new Mage_Backend_Model_Config_Loader($this->_configDataFactory);

        $this->_configCollection = $this->getMock(
            'Mage_Core_Model_Resource_Config_Data_Collection', array(), array(), '', false
        );
        $this->_configCollection->expects($this->once())->method('addScopeFilter')->with('scope', 'scopeId', 'section')
            ->will($this->returnSelf());

        $configDataMock = $this->getMock('Mage_Core_Model_Config_Data', array(), array(), '', false);
        $this->_configDataFactory->expects($this->once())->method('create')->will($this->returnValue($configDataMock));
        $configDataMock->expects($this->any())->method('getCollection')
            ->will($this->returnValue($this->_configCollection));

        $this->_configCollection->expects($this->once())->method('getItems')->will($this->returnValue(
            array(
                new Varien_Object(array('path' => 'section', 'value' => 10, 'config_id' => 20))
            )
        ));
    }

    protected function tearDown()
    {
        unset($this->_configDataFactory);
        unset($this->_model);
        unset($this->_configCollection);
    }

    public function testGetConfigByPathInFullMode()
    {
        $expected = array('section' => array('path' => 'section', 'value' => 10, 'config_id' => 20));
        $this->assertEquals($expected, $this->_model->getConfigByPath('section', 'scope', 'scopeId', true));
    }

    public function testGetConfigByPath()
    {
        $expected = array('section' => 10);
        $this->assertEquals($expected, $this->_model->getConfigByPath('section', 'scope', 'scopeId', false));
    }
}

