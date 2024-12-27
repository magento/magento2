<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\Product\Index\Compared;
use Magento\Reports\Model\Product\Index\ComparedFactory;
use Magento\Reports\Model\Product\Index\Viewed;
use Magento\Reports\Model\Product\Index\ViewedFactory;
use Magento\Reports\Observer\CustomerLogoutObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerLogoutObserverTest extends TestCase
{
    /**
     * @var CustomerLogoutObserver
     */
    protected $observer;

    /**
     * @var ComparedFactory|MockObject
     */
    protected $productCompFactoryMock;

    /**
     * @var ViewedFactory|MockObject
     */
    protected $productIndexFactoryMock;

    /**
     * @var Viewed|MockObject
     */
    protected $productIndexMock;

    /**
     * @var Compared|MockObject
     */
    protected $productCompModelMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

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

        $this->productCompModelMock = $this->getMockBuilder(Compared::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productCompFactoryMock = $this->getMockBuilder(
            ComparedFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->productCompFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->productCompModelMock);

        $this->observer = $objectManager->getObject(
            CustomerLogoutObserver::class,
            [
                'productIndxFactory' => $this->productIndexFactoryMock,
                'productCompFactory' => $this->productCompFactoryMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCustomerLogout()
    {
        $observerMock = $this->getObserverMock(111);

        $this->productCompModelMock->expects($this->once())->method('purgeVisitorByCustomer')->willReturnSelf();
        $this->productCompModelMock->expects($this->once())->method('calculate')->willReturnSelf();

        $this->productIndexMock->expects($this->once())->method('purgeVisitorByCustomer')->willReturnSelf();
        $this->productIndexMock->expects($this->once())->method('calculate')->willReturnSelf();

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
        $eventMock = $this->getMockBuilder(Event::class)
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
