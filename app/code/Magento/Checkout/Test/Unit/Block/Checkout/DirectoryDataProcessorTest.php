<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Checkout;

class DirectoryDataProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Checkout\DirectoryDataProcessor
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryDataHelperMock;

    protected function setUp()
    {
        $this->countryCollectionFactoryMock = $this->getMock(
            \Magento\Directory\Model\ResourceModel\Country\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->countryCollectionMock = $this->getMock(
            \Magento\Directory\Model\ResourceModel\Country\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->regionCollectionFactoryMock = $this->getMock(
            \Magento\Directory\Model\ResourceModel\Region\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->regionCollectionMock = $this->getMock(
            \Magento\Directory\Model\ResourceModel\Region\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->storeResolverMock = $this->getMock(
            \Magento\Store\Api\StoreResolverInterface::class
        );
        $this->directoryDataHelperMock = $this->getMock(
            \Magento\Directory\Helper\Data::class,
            [],
            [],
            '',
            false
        );

        $this->model = new \Magento\Checkout\Block\Checkout\DirectoryDataProcessor(
            $this->countryCollectionFactoryMock,
            $this->regionCollectionFactoryMock,
            $this->storeResolverMock,
            $this->directoryDataHelperMock
        );
    }

    public function testProcess()
    {
        $expectedResult['components']['checkoutProvider']['dictionaries'] = [
            'country_id' => [],
            'region_id' => [],
        ];

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
