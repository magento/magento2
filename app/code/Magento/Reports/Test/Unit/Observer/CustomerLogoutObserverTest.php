<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Test\Unit\Observer;

class CustomerLogoutObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Observer\CustomerLogoutObserver
     */
    protected $observer;

    /**
     * @var \Magento\Reports\Model\Product\Index\ComparedFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productCompFactoryMock;

    /**
     * @var \Magento\Reports\Model\Product\Index\ViewedFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productIndexFactoryMock;

    /**
     * @var \Magento\Reports\Model\Product\Index\Viewed|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productIndexMock;

    /**
     * @var \Magento\Reports\Model\Product\Index\Compared|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productCompModelMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
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
     * @return \PHPUnit_Framework_MockObject_MockObject
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
