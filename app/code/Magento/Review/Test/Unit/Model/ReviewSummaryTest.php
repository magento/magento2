<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Review\Model\ResourceModel\Review\Summary\Collection;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory;
use Magento\Review\Model\Review\Summary;
use Magento\Review\Model\ReviewSummary;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Review\Model\ReviewSummary class.
 */
class ReviewSummaryTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $reviewSummaryCollectionFactoryMock;

    /**
     * @var ReviewSummary|MockObject
     */
    private $reviewSummary;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $this->reviewSummaryCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->reviewSummary = $this->objectManagerHelper->getObject(
            ReviewSummary::class,
            [
                'sumColFactory' => $this->reviewSummaryCollectionFactoryMock
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
        $product->expects($this->once())->method('getId')->willReturn($productId);
        $product->expects($this->once())->method('addData')
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
        $summaryCollection->expects($this->once())->method('addEntityFilter')
            ->willReturnSelf();
        $summaryCollection->expects($this->once())->method('addStoreFilter')
            ->willReturnSelf();
        $summaryCollection->expects($this->once())->method('getFirstItem')
            ->willReturn($summaryData);

        $this->reviewSummaryCollectionFactoryMock->expects($this->once())->method('create')
            ->willReturn($summaryCollection);

        $this->assertNull($this->reviewSummary->appendSummaryDataToObject($product, $storeId));
    }
}
