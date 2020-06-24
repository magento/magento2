<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Backend\Model\Menu\Director\Director
 */
namespace Magento\Backend\Test\Unit\Model\Menu\Director;

use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu\Builder\AbstractCommand;
use Magento\Backend\Model\Menu\Builder\CommandFactory;
use Magento\Backend\Model\Menu\Director\Director;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DirectorTest extends TestCase
{
    /**
     * @var Director
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_commandFactoryMock;

    /**
     * @var MockObject
     */
    protected $_builderMock;

    /**
     * @var MockObject
     */
    protected $_logger;

    /**
     * @var MockObject
     */
    protected $_commandMock;

    protected function setUp(): void
    {
        $this->_builderMock = $this->createMock(Builder::class);
        $this->_logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->_commandMock = $this->createPartialMock(
            AbstractCommand::class,
            ['getId', '_execute', 'execute', 'chain']
        );
        $this->_commandFactoryMock = $this->createPartialMock(
            CommandFactory::class,
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
        $this->_model = new Director($this->_commandFactoryMock);
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
