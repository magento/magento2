<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Review\Model\ResourceModel\Rating;
use Magento\Review\Model\ResourceModel\Review;
use Magento\Review\Observer\ProcessProductAfterDeleteEventObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProcessProductAfterDeleteEventObserverTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var ProcessProductAfterDeleteEventObserver
     */
    private $observer;

    /**
     * @var Review|MockObject
     */
    private $resourceReviewMock;

    /**
     * @var Rating|MockObject
     */
    private $resourceRatingMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->resourceReviewMock = $this->createMock(Review::class);
        $this->resourceRatingMock = $this->createMock(Rating::class);

        $this->observer = new ProcessProductAfterDeleteEventObserver(
            $this->resourceReviewMock,
            $this->resourceRatingMock
        );
    }

    /**
     * Test cleanup product reviews after product delete
     *
     * @return void
     */
    public function testCleanupProductReviewsWithProduct()
    {
        $productId = 1;
        $observerMock = $this->createMock(Observer::class);
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProduct'])
            ->getMock();

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();

        $productMock->expects(self::exactly(3))
            ->method('getId')
            ->willReturn($productId);
        $eventMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);
        $this->resourceReviewMock->expects($this->once())
            ->method('deleteReviewsByProductId')
            ->willReturnSelf();
        $this->resourceRatingMock->expects($this->once())
            ->method('deleteAggregatedRatingsByProductId')
            ->willReturnSelf();

        $this->observer->execute($observerMock);
    }

    /**
     * Test with no event product
     *
     * @return void
     */
    public function testCleanupProductReviewsWithoutProduct()
    {
        $observerMock = $this->createMock(Observer::class);
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProduct'])
            ->getMock();

        $eventMock->expects($this->once())
            ->method('getProduct')
            ->willReturn(null);
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);
        $this->resourceReviewMock->expects($this->never())
            ->method('deleteReviewsByProductId')
            ->willReturnSelf();
        $this->resourceRatingMock->expects($this->never())
            ->method('deleteAggregatedRatingsByProductId')
            ->willReturnSelf();

        $this->observer->execute($observerMock);
    }
}
