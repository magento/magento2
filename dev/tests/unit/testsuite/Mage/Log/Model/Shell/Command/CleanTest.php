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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Log_Model_Shell_Command_CleanTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logMock;

    protected function setUp()
    {
        $this->_storeManagerMock = $this->getMock('Mage_Core_Model_StoreManagerInterface');
        $this->_logFactoryMock = $this->getMock('Mage_Log_Model_LogFactory', array('create'), array(), '', false);
        $this->_logMock = $this->getMock('Mage_Log_Model_Log', array(), array(), '', false);
        $this->_logFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->_logMock));
    }

    public function testExecuteWithoutDaysOffset()
    {
        $model = new Mage_Log_Model_Shell_Command_Clean($this->_storeManagerMock, $this->_logFactoryMock, 0);
        $this->_storeManagerMock->expects($this->never())->method('getStore');
        $this->_logMock->expects($this->once())->method('clean');
        $this->assertStringStartsWith('Log cleaned', $model->execute());
    }

    public function testExecuteWithDaysOffset()
    {
        $model = new Mage_Log_Model_Shell_Command_Clean($this->_storeManagerMock, $this->_logFactoryMock, 10);
        $storeMock = $this->getMock('Mage_Core_Model_Store', array(), array(), '', false);
        $this->_storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));

        $this->_logMock->expects($this->once())->method('clean');
        $storeMock->expects($this->once())->method('setConfig')->with(Mage_Log_Model_Log::XML_LOG_CLEAN_DAYS, 10);
        $this->assertStringStartsWith('Log cleaned', $model->execute());
    }
}