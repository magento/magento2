<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for Magento\Review\Model\ReviewSummary class.
 */
class ReviewSummaryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject
     */
    private $reviewSummaryCollectionFactoryMock;

    /**
     * @var \Magento\Review\Model\ReviewSummary | MockObject
     */
    private $reviewSummary;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->reviewSummaryCollectionFactoryMock = $this->createPartialMock(
            \Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory::class,
            ['create']
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->reviewSummary = $this->objectManagerHelper->getObject(
            \Magento\Review\Model\ReviewSummary::class,
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
            \Magento\Catalog\Model\Product::class,
            ['getId', 'addData', '__wakeup']
        );
        $product->expects($this->once())->method('getId')->will($this->returnValue($productId));
        $product->expects($this->once())->method('addData')
            ->with($testSummaryData)
            ->will($this->returnSelf());

        $summaryData = $this->createPartialMock(
            \Magento\Review\Model\Review\Summary::class,
            ['getData', '__wakeup']
        );
        $summaryData->expects($this->atLeastOnce())->method('getData')->will(
            $this->returnValueMap(
                [
                    ['reviews_count', null, $testSummaryData['reviews_count']],
                    ['rating_summary', null, $testSummaryData['rating_summary']]
                ]
            )
        );
        $summaryCollection = $this->createPartialMock(
            \Magento\Review\Model\ResourceModel\Review\Summary\Collection::class,
            ['addEntityFilter', 'addStoreFilter', 'getFirstItem', '__wakeup']
        );
        $summaryCollection->expects($this->once())->method('addEntityFilter')
            ->will($this->returnSelf());
        $summaryCollection->expects($this->once())->method('addStoreFilter')
            ->will($this->returnSelf());
        $summaryCollection->expects($this->once())->method('getFirstItem')
            ->will($this->returnValue($summaryData));

        $this->reviewSummaryCollectionFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($summaryCollection));

        $this->assertNull($this->reviewSummary->appendSummaryDataToObject($product, $storeId));
    }
}
