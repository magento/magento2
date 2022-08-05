<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit\Config\Data;

use Magento\Framework\Mview\Config\Data;
use Magento\Framework\Mview\Config\Data\Proxy;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProxyTest extends TestCase
{
    /**
     * @var Proxy
     */
    protected $model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Data|MockObject
     */
    protected $dataMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->dataMock = $this->createMock(Data::class);
    }

    public function testMergeShared()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Data::class)
            ->willReturn($this->dataMock);
        $this->dataMock->expects($this->once())
            ->method('merge')
            ->with(['some_config']);

        $this->model = new Proxy(
            $this->objectManagerMock,
            Data::class,
            true
        );

        $this->model->merge(['some_config']);
    }

    public function testMergeNonShared()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Data::class)
            ->willReturn($this->dataMock);
        $this->dataMock->expects($this->once())
            ->method('merge')
            ->with(['some_config']);

        $this->model = new Proxy(
            $this->objectManagerMock,
            Data::class,
            false
        );

        $this->model->merge(['some_config']);
    }

    public function testGetShared()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Data::class)
            ->willReturn($this->dataMock);
        $this->dataMock->expects($this->once())
            ->method('get')
            ->with('some_path', 'default')
            ->willReturn('some_value');

        $this->model = new Proxy(
            $this->objectManagerMock,
            Data::class,
            true
        );

        $this->assertEquals('some_value', $this->model->get('some_path', 'default'));
    }

    public function testGetNonShared()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Data::class)
            ->willReturn($this->dataMock);
        $this->dataMock->expects($this->once())
            ->method('get')
            ->with('some_path', 'default')
            ->willReturn('some_value');

        $this->model = new Proxy(
            $this->objectManagerMock,
            Data::class,
            false
        );

        $this->assertEquals('some_value', $this->model->get('some_path', 'default'));
    }
}
