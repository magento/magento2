<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Checkout;

class DirectoryDataProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Block\Checkout\DirectoryDataProcessor
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $countryCollectionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $countryCollectionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $regionCollectionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $regionCollectionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeResolverMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $directoryDataHelperMock;

    protected function setUp(): void
    {
        $this->countryCollectionFactoryMock = $this->createPartialMock(
            \Magento\Directory\Model\ResourceModel\Country\CollectionFactory::class,
            ['create']
        );
        $this->countryCollectionMock = $this->createMock(
            \Magento\Directory\Model\ResourceModel\Country\Collection::class
        );
        $this->regionCollectionFactoryMock = $this->createPartialMock(
            \Magento\Directory\Model\ResourceModel\Region\CollectionFactory::class,
            ['create']
        );
        $this->regionCollectionMock = $this->createMock(
            \Magento\Directory\Model\ResourceModel\Region\Collection::class
        );
        $this->storeResolverMock = $this->createMock(
            \Magento\Store\Api\StoreResolverInterface::class
        );
        $this->directoryDataHelperMock = $this->createMock(\Magento\Directory\Helper\Data::class);
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \Magento\Checkout\Block\Checkout\DirectoryDataProcessor(
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

        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
