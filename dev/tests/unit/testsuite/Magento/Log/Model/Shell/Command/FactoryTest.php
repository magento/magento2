<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model\Shell\Command;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Log\Model\Shell\Command\Factory
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_model = new \Magento\Log\Model\Shell\Command\Factory($this->_objectManagerMock);
    }

    public function testCreateCleanCommand()
    {
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Log\Model\Shell\Command\Clean',
            ['days' => 1]
        )->will(
            $this->returnValue($this->getMock('Magento\Log\Model\Shell\Command\Clean', [], [], '', false))
        );
        $this->isInstanceOf('Magento\Log\Model\Shell\CommandInterface', $this->_model->createCleanCommand(1));
    }

    public function testCreateStatusCommand()
    {
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Log\Model\Shell\Command\Status'
        )->will(
            $this->returnValue($this->getMock('Magento\Log\Model\Shell\Command\Status', [], [], '', false))
        );
        $this->isInstanceOf('Magento\Log\Model\Shell\CommandInterface', $this->_model->createStatusCommand());
    }
}
