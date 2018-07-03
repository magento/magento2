<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Dynamic;

use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Dynamic\IntervalInterface;
use Magento\Framework\Search\EngineResolverInterface;

class IntervalFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var IntervalFactory */
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

    public function testCreate()
    {
        $dataProvider = 'current_interval';
        $dataProviderClass = IntervalInterface::class;
        $dataProviders = [
            $dataProvider => $dataProviderClass,
        ];
        $data = ['data'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($dataProvider);

        $dataProviderMock = $this->getMockBuilder($dataProviderClass)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($dataProviderClass, $data)
            ->willReturn($dataProviderMock);

        $this->model = new IntervalFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $dataProviders
        );

        $this->assertEquals($dataProviderMock, $this->model->create($data));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Interval not found by config current_interval
     */
    public function testCreateWithoutIntervals()
    {
        $dataProvider = 'current_interval';
        $dataProviders = [];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($dataProvider);

        $this->model = new IntervalFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $dataProviders
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Interval not instance of interface
     */
    public function testCreateWithWrongInterval()
    {
        $dataProvider = 'current_interval';
        $dataProviderClass = \stdClass::class;
        $dataProviders = [
            $dataProvider => $dataProviderClass,
        ];
        $data = ['data'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($dataProvider);

        $dataProviderMock = $this->getMockBuilder($dataProviderClass)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($dataProviderClass, $data)
            ->willReturn($dataProviderMock);

        $this->model = new IntervalFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $dataProviders
        );

        $this->model->create($data);
    }
}
