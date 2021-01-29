<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit;

class ActionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Indexer\ActionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->model = new \Magento\Framework\Indexer\ActionFactory($this->objectManagerMock);
    }

    /**
     */
    public function testGetWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('NotAction doesn\'t implement \\Magento\\Framework\\Indexer\\ActionInterface');

        $notActionInterfaceMock = $this->getMockBuilder('NotAction')->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('NotAction', [])
            ->willReturn($notActionInterfaceMock);
        $this->model->create('NotAction');
    }

    public function testCreate()
    {
        $actionInterfaceMock = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\ActionInterface::class,
            [],
            '',
            false
        );
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Indexer\ActionInterface::class, [])
            ->willReturn($actionInterfaceMock);
        $this->model->create(\Magento\Framework\Indexer\ActionInterface::class);
        $this->assertInstanceOf(\Magento\Framework\Indexer\ActionInterface::class, $actionInterfaceMock);
    }
}
