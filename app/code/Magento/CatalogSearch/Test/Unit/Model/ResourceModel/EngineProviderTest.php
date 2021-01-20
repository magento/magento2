<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;

class EngineProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var EngineProvider */
    private $model;

    /** @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $objectManagerMock;

    /** @var EngineResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $engineResolverMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->engineResolverMock = $this->getMockBuilder(EngineResolverInterface::class)
            ->getMockForAbstractClass();
    }

    public function testGet()
    {
        $currentEngine = 'current_engine';
        $currentEngineClass = EngineInterface::class;
        $engines = [
            $currentEngine => $currentEngineClass,
        ];

        $this->engineResolverMock->expects($this->once())
        ->method('getCurrentSearchEngine')
        ->willReturn($currentEngine);

        $engineMock = $this->getMockBuilder($currentEngineClass)
            ->setMethods(['isAvailable'])
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($currentEngineClass)
            ->willReturn($engineMock);

        $engineMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->model = new EngineProvider(
            $this->objectManagerMock,
            $engines,
            $this->engineResolverMock
        );

        $this->assertEquals($engineMock, $this->model->get());
        $this->assertEquals($engineMock, $this->model->get());
    }

    /**
     */
    public function testGetWithoutEngines()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('There is no such engine: current_engine');

        $currentEngine = 'current_engine';
        $engines = [];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentEngine);

        $this->objectManagerMock->expects($this->never())
            ->method('create');

        $this->model = new EngineProvider(
            $this->objectManagerMock,
            $engines,
            $this->engineResolverMock
        );

        $this->model->get();
    }

    /**
     */
    public function testGetWithWrongEngine()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('current_engine doesn\'t implement');

        $currentEngine = 'current_engine';
        $currentEngineClass = \stdClass::class;
        $engines = [
            $currentEngine => $currentEngineClass,
        ];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentEngine);

        $engineMock = $this->getMockBuilder($currentEngineClass)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($currentEngineClass)
            ->willReturn($engineMock);

        $this->model = new EngineProvider(
            $this->objectManagerMock,
            $engines,
            $this->engineResolverMock
        );

        $this->model->get();
    }

    /**
     */
    public function testGetWithoutAvailableEngine()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Engine is not available: current_engine');

        $currentEngine = 'current_engine';
        $currentEngineClass = EngineInterface::class;
        $engines = [
            $currentEngine => $currentEngineClass,
        ];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentEngine);

        $engineMock = $this->getMockBuilder($currentEngineClass)
            ->setMethods(['isAvailable'])
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($currentEngineClass)
            ->willReturn($engineMock);

        $engineMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);

        $this->model = new EngineProvider(
            $this->objectManagerMock,
            $engines,
            $this->engineResolverMock
        );

        $this->model->get();
    }
}
