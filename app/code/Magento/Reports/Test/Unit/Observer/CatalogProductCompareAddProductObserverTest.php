<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Test\Unit\Observer;

class CatalogProductCompareAddProductObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Observer\CatalogProductCompareAddProductObserver
     */
    protected $observer;

    /**
     * @var \Magento\Reports\Observer\EventSaver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventSaverMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Customer\Model\Visitor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerVisitorMock;

    /**
     * @var \Magento\Reports\Model\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportEventMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Reports\Model\Product\Index\ComparedFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productCompFactoryMock;

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

        $this->customerSessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()->getMock();
        $this->customerVisitorMock = $this->getMockBuilder('Magento\Customer\Model\Visitor')
            ->disableOriginalConstructor()->getMock();

        $reportEventFactory = $this->getMockBuilder('Magento\Reports\Model\EventFactory')
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->reportEventMock = $this->getMockBuilder('Magento\Reports\Model\Event')
            ->disableOriginalConstructor()->getMock();

        $reportEventFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->reportEventMock);

        /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject $storeManager */
        $storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()->getMock();

        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->productCompModelMock = $this->getMockBuilder('Magento\Reports\Model\Product\Index\Compared')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productCompFactoryMock = $this->getMockBuilder('Magento\Reports\Model\Product\Index\ComparedFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productCompFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->productCompModelMock);

        $this->eventSaverMock = $this->getMockBuilder('\Magento\Reports\Observer\EventSaver')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->observer = $objectManager->getObject(
            'Magento\Reports\Observer\CatalogProductCompareAddProductObserver',
            [
                'productCompFactory' => $this->productCompFactoryMock,
                'customerSession' => $this->customerSessionMock,
                'customerVisitor' => $this->customerVisitorMock,
                'eventSaver' => $this->eventSaverMock
            ]
        );
    }

    /**
     * @param bool $isLoggedIn
     * @param string $userKey
     * @param int $userId
     * @dataProvider catalogProductCompareAddProductDataProvider
     * @return void
     */
    public function testCatalogProductCompareAddProduct($isLoggedIn, $userKey, $userId)
    {
        $productId = 111;
        $customerId = 222;
        $visitorId = 333;
        $viewData = [
            'product_id' => $productId,
            $userKey => $userId
        ];
        $observerMock = $this->getObserverMock($productId);

        $this->customerSessionMock->expects($this->any())->method('isLoggedIn')->willReturn($isLoggedIn);
        $this->customerSessionMock->expects($this->any())->method('getCustomerId')->willReturn($customerId);

        $this->customerVisitorMock->expects($this->any())->method('getId')->willReturn($visitorId);

        $this->productCompModelMock->expects($this->any())->method('setData')->with($viewData)->willReturnSelf();
        $this->productCompModelMock->expects($this->any())->method('save')->willReturnSelf();
        $this->productCompModelMock->expects($this->any())->method('calculate')->willReturnSelf();

        $this->eventSaverMock->expects($this->once())->method('save');

        $this->observer->execute($observerMock);
    }

    /**
     * @return array
     */
    public function catalogProductCompareAddProductDataProvider()
    {
        return [
            'logged in' => [
                'isLoggedIn' => true,
                'userKey' => 'customer_id',
                'userId' => 222
            ],
            'not logged in' => [
                'isLoggedIn' => false,
                'userKey' => 'visitor_id',
                'userId' => 333
            ]
        ];
    }

    /**
     * @param int $productId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getObserverMock($productId)
    {
        $eventObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])->getMock();
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->any())->method('getId')->willReturn($productId);

        $eventMock->expects($this->any())->method('getProduct')->willReturn($productMock);

        $eventObserverMock->expects($this->any())->method('getEvent')->willReturn($eventMock);

        return $eventObserverMock;
    }
}
