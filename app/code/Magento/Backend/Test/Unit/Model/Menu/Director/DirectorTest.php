<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Backend\Model\Menu\Director\Director
 */
namespace Magento\Backend\Test\Unit\Model\Menu\Director;

class DirectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Director\Director
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_commandFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_builderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_commandMock;

    protected function setUp()
    {
        $this->_builderMock = $this->getMock(\Magento\Backend\Model\Menu\Builder::class, [], [], '', false);
        $this->_logger = $this->getMock(\Psr\Log\LoggerInterface::class);
        $this->_commandMock = $this->getMock(
            \Magento\Backend\Model\Menu\Builder\AbstractCommand::class,
            ['getId', '_execute', 'execute', 'chain'],
            [],
            '',
            false
        );
        $this->_commandFactoryMock = $this->getMock(
            \Magento\Backend\Model\Menu\Builder\CommandFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_commandFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_commandMock)
        );

        $this->_commandMock->expects($this->any())->method('getId')->will($this->returnValue(true));
        $this->_model = new \Magento\Backend\Model\Menu\Director\Director($this->_commandFactoryMock);
    }

    public function testDirectWithExistKey()
    {
        $config = [['type' => 'update'], ['type' => 'remove'], ['type' => 'added']];
        $this->_builderMock->expects($this->at(2))->method('processCommand')->with($this->_commandMock);
        $this->_logger->expects($this->at(1))->method('debug');
        $this->_commandMock->expects($this->at(1))->method('getId');
        $this->_model->direct($config, $this->_builderMock, $this->_logger);
    }
}
