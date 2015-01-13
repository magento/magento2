<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model\Shell\Command;

class CleanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mutableConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logMock;

    protected function setUp()
    {
        $this->_mutableConfigMock = $this->getMock('Magento\Framework\App\Config\MutableScopeConfigInterface');
        $this->_logFactoryMock = $this->getMock('Magento\Log\Model\LogFactory', ['create'], [], '', false);
        $this->_logMock = $this->getMock('Magento\Log\Model\Log', [], [], '', false);
        $this->_logFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->_logMock));
    }

    public function testExecuteWithoutDaysOffset()
    {
        $model = new \Magento\Log\Model\Shell\Command\Clean($this->_mutableConfigMock, $this->_logFactoryMock, 0);
        $this->_mutableConfigMock->expects($this->never())->method('setValue');
        $this->_logMock->expects($this->once())->method('clean');
        $this->assertStringStartsWith('Log cleaned', $model->execute());
    }

    public function testExecuteWithDaysOffset()
    {
        $model = new \Magento\Log\Model\Shell\Command\Clean($this->_mutableConfigMock, $this->_logFactoryMock, 10);
        $this->_mutableConfigMock->expects($this->once())
            ->method('setValue')
            ->with(
                \Magento\Log\Model\Log::XML_LOG_CLEAN_DAYS,
                10,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

        $this->_logMock->expects($this->once())->method('clean');
        $this->assertStringStartsWith('Log cleaned', $model->execute());
    }
}
