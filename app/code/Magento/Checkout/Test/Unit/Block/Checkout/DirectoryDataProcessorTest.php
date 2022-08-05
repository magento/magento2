<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Checkout;

use Magento\Checkout\Block\Checkout\DirectoryDataProcessor;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DirectoryDataProcessorTest extends TestCase
{
    /**
     * @var DirectoryDataProcessor
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $countryCollectionFactoryMock;

    /**
     * @var MockObject
     */
    protected $countryCollectionMock;

    /**
     * @var MockObject
     */
    protected $regionCollectionFactoryMock;

    /**
     * @var MockObject
     */
    protected $regionCollectionMock;

    /**
     * @var MockObject
     */
    protected $storeResolverMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    private $directoryDataHelperMock;

    protected function setUp(): void
    {
        $this->countryCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->countryCollectionMock = $this->createMock(
            Collection::class
        );
        $this->regionCollectionFactoryMock = $this->createPartialMock(
            \Magento\Directory\Model\ResourceModel\Region\CollectionFactory::class,
            ['create']
        );
        $this->regionCollectionMock = $this->createMock(
            \Magento\Directory\Model\ResourceModel\Region\Collection::class
        );
        $this->storeResolverMock = $this->createMock(
            StoreResolverInterface::class
        );
        $this->directoryDataHelperMock = $this->createMock(Data::class);
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = new DirectoryDataProcessor(
            $this->countryCollectionFactoryMock,
            $this->regionCollectionFactoryMock,
            $this->storeResolverMock,
            $this->directoryDataHelperMock,
            $this->storeManagerMock
        );
    }

    public function testProcess()
    {
        $expectedResult['components']['checkoutProvider']['dictionaries'] = [
            'country_id' => [],
            'region_id' => [],
        ];

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeMock->expects($this->atLeastOnce())->method('getId')->willReturn(42);
        $this->storeManagerMock->expects($this->atLeastOnce())->method('getStore')->willReturn($storeMock);

        $this->countryCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->countryCollectionMock);
        $this->countryCollectionMock->expects($this->once())->method('loadByStore')->willReturnSelf();
        $this->countryCollectionMock->expects($this->once())->method('toOptionArray')->willReturn([]);
        $this->regionCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->regionCollectionMock);
        $this->regionCollectionMock->expects($this->once())->method('addAllowedCountriesFilter')->willReturnSelf();
        $this->regionCollectionMock->expects($this->once())->method('toOptionArray')->willReturn([]);

        $this->assertEquals($expectedResult, $this->model->process([]));
    }
}
