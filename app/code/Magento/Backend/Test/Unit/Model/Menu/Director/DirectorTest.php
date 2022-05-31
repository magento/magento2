<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Menu\Director;

use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu\Builder\AbstractCommand;
use Magento\Backend\Model\Menu\Builder\CommandFactory;
use Magento\Backend\Model\Menu\Director\Director;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for \Magento\Backend\Model\Menu\Director\Director
 */
class DirectorTest extends TestCase
{
    /**
     * @var Director
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $commandFactoryMock;

    /**
     * @var MockObject
     */
    protected $builderMock;

    /**
     * @var MockObject
     */
    protected $logger;

    /**
     * @var MockObject
     */
    protected $commandMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->builderMock = $this->createMock(Builder::class);
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->commandMock = $this->createPartialMock(
            AbstractCommand::class,
            ['getId', '_execute', 'execute', 'chain']
        );
        $this->commandFactoryMock = $this->createPartialMock(
            CommandFactory::class,
            ['create']
        );
        $this->commandFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->willReturn(
            $this->commandMock
        );

        $this->commandMock->expects($this->any())->method('getId')->willReturn(true);
        $this->model = new Director($this->commandFactoryMock);
    }

    /**
     * @return void
     */
    public function testDirectWithExistKey(): void
    {
        $config = [['type' => 'update'], ['type' => 'remove'], ['type' => 'added']];
        $this->builderMock
            ->method('processCommand')
            ->with($this->commandMock);
        $this->logger
            ->method('debug');
        $this->commandMock
            ->method('getId');
        $this->model->direct($config, $this->builderMock, $this->logger);
    }
}
