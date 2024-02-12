<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\Event;
use Magento\Reports\Model\EventFactory;
use Magento\Reports\Model\Product\Index\Compared;
use Magento\Reports\Model\Product\Index\ComparedFactory;
use Magento\Reports\Model\Product\Index\Viewed;
use Magento\Reports\Model\Product\Index\ViewedFactory;
use Magento\Reports\Observer\CustomerLoginObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerLoginObserverTest extends TestCase
{
    /**
     * @var CustomerLoginObserver
     */
    protected $observer;

    /**
     * @var Session|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var Visitor|MockObject
     */
    protected $customerVisitorMock;

    /**
     * @var Viewed|MockObject
     */
    protected $productIndexMock;

    /**
     * @var Event|MockObject
     */
    protected $reportEventMock;

    /**
     * @var ComparedFactory|MockObject
     */
    protected $productCompFactoryMock;

    /**
     * @var Compared|MockObject
     */
    protected $productCompModelMock;

    /**
     * @var ViewedFactory|MockObject
     */
    protected $productIndexFactoryMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerVisitorMock = $this->getMockBuilder(Visitor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productIndexFactoryMock = $this->getMockBuilder(
            ViewedFactory::class
        )->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productIndexMock = $this->getMockBuilder(Viewed::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productIndexFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->productIndexMock);

        $reportEventFactory = $this->getMockBuilder(EventFactory::class)
            ->onlyMethods(['create'])->disableOriginalConstructor()
            ->getMock();
        $this->reportEventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reportEventFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->reportEventMock);

        $this->productCompModelMock = $this->getMockBuilder(Compared::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productCompFactoryMock = $this->getMockBuilder(
            ComparedFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->productCompFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->productCompModelMock);

        $this->observer = $objectManager->getObject(
            CustomerLoginObserver::class,
            [
                'event' => $reportEventFactory,
                'productCompFactory' => $this->productCompFactoryMock,
                'productIndexFactory' => $this->productIndexFactoryMock,
                'customerSession' => $this->customerSessionMock,
                'customerVisitor' => $this->customerVisitorMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCustomerLoginLoggedInTrue()
    {
        $customerId = 222;
        $visitorId = 333;
        $observerMock = $this->getObserverMock(111);

        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);

        $this->customerVisitorMock->expects($this->once())->method('getId')->willReturn($visitorId);

        $this->reportEventMock->expects($this->once())->method('updateCustomerType')->with($visitorId, $customerId);

        $this->productCompModelMock->expects($this->once())->method('updateCustomerFromVisitor')->willReturnSelf();
        $this->productCompModelMock->expects($this->once())->method('calculate')->willReturnSelf();

        $this->productIndexMock->expects($this->once())->method('updateCustomerFromVisitor')->willReturnSelf();
        $this->productIndexMock->expects($this->once())->method('calculate')->willReturnSelf();

        $this->observer->execute($observerMock);
    }

    /**
     * @return void
     */
    public function testCustomerLoginLoggedInFalse()
    {
        $observerMock = $this->getObserverMock(111);

        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->customerSessionMock->expects($this->never())->method('getCustomerId');

        $this->customerVisitorMock->expects($this->never())->method('getId');

        $this->productCompModelMock->expects($this->never())->method('updateCustomerFromVisitor')->willReturnSelf();
        $this->productCompModelMock->expects($this->never())->method('calculate')->willReturnSelf();

        $this->productIndexMock->expects($this->never())->method('updateCustomerFromVisitor')->willReturnSelf();
        $this->productIndexMock->expects($this->never())->method('calculate')->willReturnSelf();

        $this->observer->execute($observerMock);
    }

    /**
     * @param int $productId
     * @return MockObject
     */
    protected function getObserverMock($productId)
    {
        $eventObserverMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProduct'])->getMock();
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->any())->method('getId')->willReturn($productId);

        $eventMock->expects($this->any())->method('getProduct')->willReturn($productMock);

        $eventObserverMock->expects($this->any())->method('getEvent')->willReturn($eventMock);

        return $eventObserverMock;
    }
}
