<?php
/**
 * Test class for Mage_Core_Model_Config_Loader_Locales
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

class Mage_Core_Model_Config_Loader_LocalesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Loader_Locales
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirsMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    protected function setUp()
    {
        $this->_dirsMock = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false, false);
        $this->_baseConfigMock = $this->getMock('Mage_Core_Model_Config_Base', array(), array(), '', false, false);
        $this->_factoryMock = $this->getMock('Mage_Core_Model_Config_BaseFactory', array(), array(), '', false, false);
        $this->_model = new Mage_Core_Model_Config_Loader_Locales(
            $this->_dirsMock,
            $this->_factoryMock
        );
    }

    protected function tearDown()
    {
        unset($this->_dirsMock);
        unset($this->_factoryMock);
        unset($this->_baseConfigMock);
        unset($this->_model);
    }

    public function testLoad()
    {
        $this->_dirsMock->expects(
            $this->once())->method('getDir')->will($this->returnValue( __DIR__ . '/../_files/locale')
        );
        $mergeMock = $this->getMock('Mage_Core_Model_Config_Base', array(), array(), '', false, false);
        $mergeMock->expects($this->exactly(4))->method('loadFile')->with($this->stringEndsWith('config.xml'));
        $this->_factoryMock->expects($this->exactly(4))->method('create')->will($this->returnValue($mergeMock));
        $this->_model->load($this->_baseConfigMock);
    }

    public function testLoadConditions()
    {
        $this->_dirsMock->expects($this->once())
            ->method('getDir')
            ->will($this->returnValue(__DIR__ . '/_files/locale/etc/etc/'));
        $this->_factoryMock->expects($this->never())->method('create');
        $this->_baseConfigMock->expects($this->never())->method('extend');
        $this->_model->load($this->_baseConfigMock);
    }
}
