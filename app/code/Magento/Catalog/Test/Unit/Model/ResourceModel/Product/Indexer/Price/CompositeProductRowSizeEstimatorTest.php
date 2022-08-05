<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CompositeProductRelationsCalculator;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CompositeProductRowSizeEstimator;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Store\Api\WebsiteManagementInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeProductRowSizeEstimatorTest extends TestCase
{
    /**
     * @var CompositeProductRowSizeEstimator
     */
    private $model;

    /**
     * @var MockObject
     */
    private $websiteManagementMock;

    /**
     * @var MockObject
     */
    private $relationsCalculatorMock;

    /**
     * @var MockObject
     */
    private $collectionFactoryMock;

    protected function setUp(): void
    {
        $this->websiteManagementMock = $this->getMockForAbstractClass(WebsiteManagementInterface::class);
        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->relationsCalculatorMock = $this->createMock(
            CompositeProductRelationsCalculator::class
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
        $collectionMock = $this->createMock(Collection::class);
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
