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

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManagerMock;

    /** @var EngineResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $engineResolverMock;

    protected function setUp()
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
     * @expectedException \LogicException
     * @expectedExceptionMessage There is no such engine: current_engine
     */
    public function testGetWithoutEngines()
    {
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
     * @expectedException \LogicException
     * @expectedExceptionMessage current_engine doesn't implement
     */
    public function testGetWithWrongEngine()
    {
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
     * @expectedException \LogicException
     * @expectedExceptionMessage Engine is not available: current_engine
     */
    public function testGetWithoutAvailableEngine()
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
            ->willReturn(false);

        $this->model = new EngineProvider(
            $this->objectManagerMock,
            $engines,
            $this->engineResolverMock
        );

        $this->model->get();
    }
}
