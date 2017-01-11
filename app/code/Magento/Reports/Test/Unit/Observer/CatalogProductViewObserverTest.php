<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Test\Unit\Observer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CatalogProductViewObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Observer\CatalogProductViewObserver
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
     * @var \Magento\Reports\Model\Product\Index\Viewed|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productIndexMock;

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
     * @var \Magento\Reports\Model\Product\Index\ViewedFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productIndexFactoryMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerVisitorMock = $this->getMockBuilder(\Magento\Customer\Model\Visitor::class)
            ->disableOriginalConstructor()->getMock();

        $this->productIndexFactoryMock = $this->getMockBuilder(
            \Magento\Reports\Model\Product\Index\ViewedFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->productIndexMock = $this->getMockBuilder(\Magento\Reports\Model\Product\Index\Viewed::class)
            ->disableOriginalConstructor()->getMock();

        $this->productIndexFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->productIndexMock);

        $reportEventFactory = $this->getMockBuilder(\Magento\Reports\Model\EventFactory::class)
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->reportEventMock = $this->getMockBuilder(\Magento\Reports\Model\Event::class)
            ->disableOriginalConstructor()->getMock();

        $reportEventFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->reportEventMock);

        /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject $storeManager */
        $storeManager = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()->getMock();

        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

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

        $this->productCompFactoryMock = $this->getMockBuilder(
            \Magento\Reports\Model\Product\Index\ComparedFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->eventSaverMock = $this->getMockBuilder(\Magento\Reports\Observer\EventSaver::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->observer = $objectManager->getObject(
            \Magento\Reports\Observer\CatalogProductViewObserver::class,
            [
                'storeManager' => $storeManager,
                'productIndxFactory' => $this->productIndexFactoryMock,
                'customerSession' => $this->customerSessionMock,
                'customerVisitor' => $this->customerVisitorMock,
                'eventSaver' => $this->eventSaverMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testCatalogProductViewCustomer()
    {
        $productId = 5;
        $customerId = 77;
        $storeId = 1;
        $expectedViewedData = [
            'product_id' => $productId,
            'customer_id' => $customerId,
            'store_id' => $storeId,
        ];

        $expectedEventData = [
            'event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW,
            'object_id' => $productId,
            'subject_id' => $customerId,
            'subtype' => 0,
            'store_id' => $storeId,
        ];

        $this->storeMock->expects($this->any())->method('getId')->willReturn($storeId);

        $this->customerSessionMock->expects($this->any())->method('isLoggedIn')->willReturn(true);
        $this->customerSessionMock->expects($this->any())->method('getCustomerId')->willReturn($customerId);

        $this->prepareProductIndexMock($expectedViewedData);
        $this->prepareReportEventModel($expectedEventData);

        $this->eventSaverMock->expects($this->once())->method('save');

        $eventObserver = $this->getObserverMock($productId);
        $this->observer->execute($eventObserver);
    }

    /**
     * @return void
     */
    public function testCatalogProductViewVisitor()
    {
        $productId = 6;
        $visitorId = 88;
        $storeId = 1;
        $expectedViewedData = [
            'product_id' => $productId,
            'visitor_id' => $visitorId,
            'store_id' => $storeId,
        ];

        $expectedEventData = [
            'event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW,
            'object_id' => $productId,
            'subject_id' => $visitorId,
            'subtype' => 1,
            'store_id' => $storeId,
        ];

        $this->storeMock->expects($this->any())->method('getId')->willReturn($storeId);

        $this->customerSessionMock->expects($this->any())->method('isLoggedIn')->willReturn(false);

        $this->customerVisitorMock->expects($this->any())->method('getId')->willReturn($visitorId);

        $this->eventSaverMock->expects($this->once())->method('save');

        $this->prepareProductIndexMock($expectedViewedData);
        $this->prepareReportEventModel($expectedEventData);
        $eventObserver = $this->getObserverMock($productId);
        $this->observer->execute($eventObserver);
    }

    /**
     * @param array $expectedViewedData
     * @return void
     */
    protected function prepareProductIndexMock($expectedViewedData)
    {
        $this->productIndexMock->expects($this->any())
            ->method('setData')
            ->with($expectedViewedData)
            ->willReturnSelf();

        $this->productIndexMock->expects($this->any())
            ->method('save')
            ->willReturnSelf();

        $this->productIndexMock->expects($this->any())
            ->method('calculate')
            ->willReturnSelf();
    }

    /**
     * @param array $expectedEventData
     * @return void
     */
    protected function prepareReportEventModel($expectedEventData)
    {
        $this->reportEventMock->expects($this->any())->method('setData')->with($expectedEventData)->willReturnSelf();
        $this->reportEventMock->expects($this->any())->method('save')->willReturnSelf();
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
