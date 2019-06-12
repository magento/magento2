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
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class ProcessProductAfterDeleteEventObserverTest
 */
class ProcessProductAfterDeleteEventObserverTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var ProcessProductAfterDeleteEventObserver
     */
    private $observer;

    /**
     * @var Review|PHPUnit_Framework_MockObject_MockObject
     */
<<<<<<< HEAD
    private $_resourceReviewMock;
=======
    private $resourceReviewMock;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

    /**
     * @var Rating|PHPUnit_Framework_MockObject_MockObject
     */
<<<<<<< HEAD
    private $_resourceRatingMock;
=======
    private $resourceRatingMock;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

    /**
     * Set up
     */
    protected function setUp()
    {
<<<<<<< HEAD
        $this->_resourceReviewMock = $this->createMock(Review::class);
        $this->_resourceRatingMock = $this->createMock(Rating::class);

        $this->observer = new ProcessProductAfterDeleteEventObserver(
            $this->_resourceReviewMock,
            $this->_resourceRatingMock
=======
        $this->resourceReviewMock = $this->createMock(Review::class);
        $this->resourceRatingMock = $this->createMock(Rating::class);

        $this->observer = new ProcessProductAfterDeleteEventObserver(
            $this->resourceReviewMock,
            $this->resourceRatingMock
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
            ->setMethods(['getProduct'])
            ->getMock();

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
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
<<<<<<< HEAD
        $this->_resourceReviewMock->expects($this->once())
            ->method('deleteReviewsByProductId')
            ->willReturnSelf();
        $this->_resourceRatingMock->expects($this->once())
=======
        $this->resourceReviewMock->expects($this->once())
            ->method('deleteReviewsByProductId')
            ->willReturnSelf();
        $this->resourceRatingMock->expects($this->once())
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
            ->setMethods(['getProduct'])
            ->getMock();

        $eventMock->expects($this->once())
            ->method('getProduct')
            ->willReturn(null);
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);
<<<<<<< HEAD
        $this->_resourceReviewMock->expects($this->never())
            ->method('deleteReviewsByProductId')
            ->willReturnSelf();
        $this->_resourceRatingMock->expects($this->never())
=======
        $this->resourceReviewMock->expects($this->never())
            ->method('deleteReviewsByProductId')
            ->willReturnSelf();
        $this->resourceRatingMock->expects($this->never())
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->method('deleteAggregatedRatingsByProductId')
            ->willReturnSelf();

        $this->observer->execute($observerMock);
    }
}
