<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Dynamic;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Dynamic\DataProviderFactory;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\EngineResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataProviderFactoryTest extends TestCase
{
    /** @var DataProviderFactory */
    private $model;

    /** @var ObjectManagerInterface|MockObject */
    private $objectManagerMock;

    /** @var EngineResolverInterface|MockObject */
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
        $dataProvider = 'current_provider';
        $dataProviderClass = DataProviderInterface::class;
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

        $this->model = new DataProviderFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $dataProviders
        );

        $this->assertEquals($dataProviderMock, $this->model->create($data));
    }

    public function testCreateWithoutProviders()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('DataProvider not found by config current_provider');
        $dataProvider = 'current_provider';
        $dataProviders = [];
        $data = ['data'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($dataProvider);

        $this->model = new DataProviderFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $dataProviders
        );

        $this->model->create($data);
    }

    public function testCreateWithWrongProvider()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('DataProvider not instance of interface');
        $dataProvider = 'current_provider';
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

        $this->model = new DataProviderFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $dataProviders
        );

        $this->model->create($data);
    }
}
