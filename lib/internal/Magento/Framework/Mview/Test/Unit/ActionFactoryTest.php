<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit;

use Magento\Framework\Mview\ActionFactory;
use Magento\Framework\Mview\ActionInterface;
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
        $this->expectExceptionMessage('NotAction doesn\'t implement \Magento\Framework\Mview\ActionInterface');
        $notActionInterfaceMock = $this->getMockBuilder('Action')
            ->getMock();
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'NotAction'
        )->willReturn(
            $notActionInterfaceMock
        );
        $this->model->get('NotAction');
    }

    public function testGet()
    {
        $actionInterfaceMock = $this->getMockForAbstractClass(
            ActionInterface::class,
            [],
            '',
            false
        );
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            ActionInterface::class
        )->willReturn(
            $actionInterfaceMock
        );
        $this->model->get(ActionInterface::class);
        $this->assertInstanceOf(ActionInterface::class, $actionInterfaceMock);
    }
}
