<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

class IndexTableRowSizeEstimatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableRowSizeEstimator
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    protected function setUp()
    {
        $this->websiteManagementMock = $this->getMock(\Magento\Store\Api\WebsiteManagementInterface::class);
        $this->collectionFactoryMock = $this->getMock(
            \Magento\Customer\Model\ResourceModel\Group\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableRowSizeEstimator(
            $this->websiteManagementMock,
            $this->collectionFactoryMock
        );
    }

    public function testEstimateRowSize()
    {
        $expectedValue = 2400000;

        $this->websiteManagementMock->expects($this->once())->method('getCount')->willReturn(100);
        $collectionMock = $this->getMock(
            \Magento\Customer\Model\ResourceModel\Group\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('getSize')->willReturn(200);

        $this->assertEquals(
            $expectedValue,
            $this->model->estimateRowSize()
        );
    }
}
