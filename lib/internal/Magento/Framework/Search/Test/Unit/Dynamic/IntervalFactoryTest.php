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
     */
    public function testCreateWithoutIntervals()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Interval not found by config current_interval');

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
     */
    public function testCreateWithWrongInterval()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Interval not instance of interface');

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
