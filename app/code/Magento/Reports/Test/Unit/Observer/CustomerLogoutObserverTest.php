<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Test\Unit\Observer;

class CustomerLogoutObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Reports\Observer\CustomerLogoutObserver
     */
    protected $observer;

    /**
     * @var \Magento\Reports\Model\Product\Index\ComparedFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productCompFactoryMock;

    /**
     * @var \Magento\Reports\Model\Product\Index\ViewedFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productIndexFactoryMock;

    /**
     * @var \Magento\Reports\Model\Product\Index\Viewed|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productIndexMock;

    /**
     * @var \Magento\Reports\Model\Product\Index\Compared|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productCompModelMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->productIndexFactoryMock = $this->getMockBuilder(
            \Magento\Reports\Model\Product\Index\ViewedFactory::class
        )->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->productIndexMock = $this->getMockBuilder(\Magento\Reports\Model\Product\Index\Viewed::class)
            ->disableOriginalConstructor()->getMock();

        $this->productIndexFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->productIndexMock);

        $this->productCompModelMock = $this->getMockBuilder(\Magento\Reports\Model\Product\Index\Compared::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productCompFactoryMock = $this->getMockBuilder(
            \Magento\Reports\Model\Product\Index\ComparedFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productCompFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->productCompModelMock);

        $this->observer = $objectManager->getObject(
            \Magento\Reports\Observer\CustomerLogoutObserver::class,
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
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getObserverMock($productId)
    {
        $eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])->getMock();
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->any())->method('getId')->willReturn($productId);

        $eventMock->expects($this->any())->method('getProduct')->willReturn($productMock);

        $eventObserverMock->expects($this->any())->method('getEvent')->willReturn($eventMock);

        return $eventObserverMock;
    }
}
