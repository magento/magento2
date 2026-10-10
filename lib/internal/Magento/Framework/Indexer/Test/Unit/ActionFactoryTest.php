<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->model = new ActionFactory($this->objectManagerMock);
    }

    public function testGetWithException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('stdClass doesn\'t implement \Magento\Framework\Indexer\ActionInterface');
        $notActionInterfaceMock = $this->createMock(\stdClass::class);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\stdClass::class, [])
            ->willReturn($notActionInterfaceMock);
        $this->model->create(\stdClass::class);
    }

    public function testCreate()
    {
        $actionInterfaceMock = $this->createMock(
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
