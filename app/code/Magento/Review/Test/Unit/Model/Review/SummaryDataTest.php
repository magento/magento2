<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Model\Review;

use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Review\Model\ResourceModel\Review as ReviewResource;
use Magento\Review\Model\ResourceModel\Review\Summary\Collection;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\Review\Summary;
use Magento\Review\Model\Review\SummaryData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Review\Model\Review\SummaryData class.
 */
class SummaryDataTest extends TestCase
{
    /**
     * @var SummaryData
     */
    private $summaryData;

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

        $objectManager = new ObjectManager($this);
        $this->summaryData = $objectManager->getObject(
            SummaryData::class,
            [
                'sumColFactory' => $this->reviewSummaryCollectionFactoryMock,
                'reviewResource' => $this->reviewResourceMock
            ]
        );
    }

    public function testAppendSummaryDataToObject()
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
        $product->expects(self::once())->method('getId')->willReturn($productId);
        $product->expects(self::once())
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
        $summaryCollection->expects(self::once())
            ->method('addEntityFilter')
            ->willReturnSelf();
        $summaryCollection->expects(self::once())
            ->method('addStoreFilter')
            ->willReturnSelf();
        $summaryCollection->expects(self::once())
            ->method('getFirstItem')
            ->willReturn($summaryData);

        $this->reviewResourceMock->expects(self::once())
            ->method('getEntityIdByCode')
            ->with(Review::ENTITY_PRODUCT_CODE)
            ->willReturn(1);
        $this->reviewSummaryCollectionFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($summaryCollection);

        $this->summaryData->appendSummaryDataToObject($product, $storeId);
    }
}
