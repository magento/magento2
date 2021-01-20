<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CompositeProductRowSizeEstimator;

class CompositeProductRowSizeEstimatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompositeProductRowSizeEstimator
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $websiteManagementMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $relationsCalculatorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $collectionFactoryMock;

    protected function setUp(): void
    {
        $this->websiteManagementMock = $this->createMock(\Magento\Store\Api\WebsiteManagementInterface::class);
        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\Customer\Model\ResourceModel\Group\CollectionFactory::class,
            ['create']
        );
        $this->relationsCalculatorMock = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CompositeProductRelationsCalculator::class
        );
        $this->model = new CompositeProductRowSizeEstimator(
            $this->websiteManagementMock,
            $this->collectionFactoryMock,
            $this->relationsCalculatorMock
        );
    }

    public function testEstimateRowSize()
    {
        $expectedResult = 40000000;
        $maxRelatedProductCount = 10;

        $this->websiteManagementMock->expects($this->once())->method('getCount')->willReturn(100);
        $collectionMock = $this->createMock(\Magento\Customer\Model\ResourceModel\Group\Collection::class);
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('getSize')->willReturn(200);
        $this->relationsCalculatorMock->expects($this->once())
            ->method('getMaxRelationsCount')
            ->willReturn($maxRelatedProductCount);

        $this->assertEquals(
            $expectedResult,
            $this->model->estimateRowSize()
        );
    }
}
