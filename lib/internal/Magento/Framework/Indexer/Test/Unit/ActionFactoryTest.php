<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Test\Unit;

use Magento\Framework\Indexer\ActionFactory;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ActionFactoryTest extends TestCase
{
    /**
     * @var ActionFactory|MockObject
     */
    protected $model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->model = new ActionFactory($this->objectManagerMock);
    }

    public function testGetWithException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('NotAction doesn\'t implement \Magento\Framework\Indexer\ActionInterface');
        $notActionInterfaceMock = $this->getMockBuilder('NotAction')
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('NotAction', [])
            ->willReturn($notActionInterfaceMock);
        $this->model->create('NotAction');
    }

    public function testCreate()
    {
        $actionInterfaceMock = $this->getMockForAbstractClass(
            ActionInterface::class,
            [],
            '',
            false
        );
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(ActionInterface::class, [])
            ->willReturn($actionInterfaceMock);
        $this->model->create(ActionInterface::class);
        $this->assertInstanceOf(ActionInterface::class, $actionInterfaceMock);
    }
}
