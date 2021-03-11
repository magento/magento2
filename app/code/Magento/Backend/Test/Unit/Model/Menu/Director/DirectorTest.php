<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Backend\Model\Menu\Director\Director
 */
namespace Magento\Backend\Test\Unit\Model\Menu\Director;

class DirectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Director\Director
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_commandFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_builderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_commandMock;

    protected function setUp(): void
    {
        $this->_builderMock = $this->createMock(\Magento\Backend\Model\Menu\Builder::class);
        $this->_logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->_commandMock = $this->createPartialMock(
            \Magento\Backend\Model\Menu\Builder\AbstractCommand::class,
            ['getId', '_execute', 'execute', 'chain']
        );
        $this->_commandFactoryMock = $this->createPartialMock(
            \Magento\Backend\Model\Menu\Builder\CommandFactory::class,
            ['create']
        );
        $this->_commandFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->willReturn(
            $this->_commandMock
        );

        $this->_commandMock->expects($this->any())->method('getId')->willReturn(true);
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
