<?php
/**
 * Test class for Mage_Core_Model_Config_Fieldset
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Config_FieldsetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_Config_Modules_Reader
     */
    protected $_configReaderMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_Cache_Type_Config
     */
    protected $_cacheTypeMock;

    protected function setUp()
    {
        $this->_configReaderMock = $this->getMock('Mage_Core_Model_Config_Modules_Reader', array(), array(), '', false);
        $this->_cacheTypeMock = $this->getMock('Mage_Core_Model_Cache_Type_Config', array(), array(), '', false);
    }

    protected function tearDown()
    {
        $this->_configReaderMock = null;
        $this->_cacheTypeMock = null;
    }

    public function testConstructorCacheExists()
    {
        $cachedConfig = '<config/>';
        $this->_cacheTypeMock->expects($this->once())
            ->method('load')
            ->with('fieldset_config')
            ->will($this->returnValue($cachedConfig));
        $model = new Mage_Core_Model_Config_Fieldset($this->_configReaderMock, $this->_cacheTypeMock);
        $this->assertInstanceOf('Varien_Simplexml_Element', $model->getNode());
    }

    public function testConstructorNoCacheExists()
    {
        $config = new Mage_Core_Model_Config_Base('<config/>');
        $this->_cacheTypeMock->expects($this->once())
            ->method('load')
            ->with('fieldset_config')
            ->will($this->returnValue(false));
        $this->_configReaderMock->expects($this->once())
            ->method('loadModulesConfiguration')
            ->with('fieldset.xml')
            ->will($this->returnValue($config));
        $this->_cacheTypeMock->expects($this->once())
            ->method('save')
            ->with("<?xml version=\"1.0\"?>\n<config/>\n");
        $model = new Mage_Core_Model_Config_Fieldset($this->_configReaderMock, $this->_cacheTypeMock);
        $this->assertInstanceOf('Varien_Simplexml_Element', $model->getNode());
    }
}
