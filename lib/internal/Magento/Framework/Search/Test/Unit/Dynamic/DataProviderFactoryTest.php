<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Dynamic;

use Magento\Framework\Search\Dynamic\DataProviderFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\EngineResolverInterface;

class DataProviderFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var DataProviderFactory */
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

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage DataProvider not found by config current_provider
     */
    public function testCreateWithoutProviders()
    {
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

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage DataProvider not instance of interface
     */
    public function testCreateWithWrongProvider()
    {
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
