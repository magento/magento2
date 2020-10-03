<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Model\Review;

use Magento\Catalog\Model\Product;
use Magento\Review\Model\ResourceModel\Review as ReviewResource;
use Magento\Review\Model\ResourceModel\Review\Summary\Collection;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\Review\Summary;
use Magento\Review\Model\Review\AppendSummaryDataToObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AppendSummaryDataToObjectTest extends TestCase
{
    /**
     * @var AppendSummaryDataToObject
     */
    private $model;

    /**
     * @var CollectionFactory|MockObject
     */
    private $reviewSummaryCollectionFactoryMock;

    /**
     * @var ReviewResource|MockObject
     */
    private $reviewResourceMock;

    protected function setUp(): void
    {
        $this->reviewSummaryCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->reviewResourceMock = $this->createPartialMock(
            ReviewResource::class,
            ['getEntityIdByCode']
        );

        $this->model = new AppendSummaryDataToObject(
            $this->reviewSummaryCollectionFactoryMock,
            $this->reviewResourceMock
        );
    }

    public function testExecute()
    {
        $productId = 6;
        $storeId = 4;
        $testSummaryData = [
            'reviews_count' => 2,
            'rating_summary' => 80
        ];
        $product = $this->createPartialMock(
            Product::class,
            ['getId', 'addData', '__wakeup']
        );
        $product->expects($this->once())->method('getId')->willReturn($productId);
        $product->expects($this->once())
            ->method('addData')
            ->with($testSummaryData)
            ->willReturnSelf();

        $summaryData = $this->createPartialMock(
            Summary::class,
            ['getData', '__wakeup']
        );
        $summaryData->expects($this->atLeastOnce())->method('getData')->willReturnMap(
            [
                ['reviews_count', null, $testSummaryData['reviews_count']],
                ['rating_summary', null, $testSummaryData['rating_summary']]
            ]
        );
        $summaryCollection = $this->createPartialMock(
            Collection::class,
            ['addEntityFilter', 'addStoreFilter', 'getFirstItem', '__wakeup']
        );
        $summaryCollection->expects($this->once())
            ->method('addEntityFilter')
            ->willReturnSelf();
        $summaryCollection->expects($this->once())
            ->method('addStoreFilter')
            ->willReturnSelf();
        $summaryCollection->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($summaryData);

        $this->reviewResourceMock->expects($this->once())
            ->method('getEntityIdByCode')
            ->with(Review::ENTITY_PRODUCT_CODE)
            ->willReturn(1);
        $this->reviewSummaryCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($summaryCollection);

        $this->model->execute($product, $storeId);
    }
}
