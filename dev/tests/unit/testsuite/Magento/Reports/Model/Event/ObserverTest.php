<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Reports\Model\Event;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\Event\Observer
     */
    protected $observer;

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

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->customerSessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()->getMock();
        $this->customerVisitorMock = $this->getMockBuilder('Magento\Customer\Model\Visitor')
            ->disableOriginalConstructor()->getMock();

        $productIndexFactoryMock = $this->getMockBuilder('Magento\Reports\Model\Product\Index\ViewedFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->productIndexMock = $this->getMockBuilder('Magento\Reports\Model\Product\Index\Viewed')
            ->disableOriginalConstructor()->getMock();

        $productIndexFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->productIndexMock);

        $reportEventFactory = $this->getMockBuilder('Magento\Reports\Model\EventFactory')
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->reportEventMock = $this->getMockBuilder('Magento\Reports\Model\Event')
            ->disableOriginalConstructor()->getMock();

        $reportEventFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->reportEventMock);

        /** @var \Magento\Store\Model\StoreManagerInterfac|\PHPUnit_Framework_MockObject_MockObject $storeManager */
        $storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');

        $this->storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()->getMock();

        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->observer = $objectManager->getObject(
            'Magento\Reports\Model\Event\Observer',
            [
                'customerSession' => $this->customerSessionMock,
                'customerVisitor' => $this->customerVisitorMock,
                'productIndxFactory' => $productIndexFactoryMock,
                'storeManager' => $storeManager,
                'event' => $reportEventFactory
            ]
        );
    }

    public function testCatalogProductViewCustomer()
    {
        $productId = 5;
        $customerId = 77;
        $storeId = 1;
        $expectedViewedData = [
            'product_id' => $productId,
            'customer_id' => $customerId
        ];

        $expectedEventData = [
            'event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW,
            'object_id' => $productId,
            'subject_id' => $customerId,
            'subtype' => 0,
            'store_id' => $storeId,
        ];

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->customerSessionMock->expects($this->any())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->prepareProductIndexMock($expectedViewedData);
        $this->prepareReportEventModel($expectedEventData);
        $eventObserver = $this->getObserverMock($productId);
        $this->observer->catalogProductView($eventObserver);
    }

    public function testCatalogProductViewVisitor()
    {
        $productId = 6;
        $visitorId = 88;
        $storeId = 1;
        $expectedViewedData = [
            'product_id' => $productId,
            'visitor_id' => $visitorId
        ];

        $expectedEventData = [
            'event_type_id' => \Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW,
            'object_id' => $productId,
            'subject_id' => $visitorId,
            'subtype' => 1,
            'store_id' => $storeId,
        ];

        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->customerVisitorMock->expects($this->any())
            ->method('getId')
            ->willReturn($visitorId);

        $this->prepareProductIndexMock($expectedViewedData);
        $this->prepareReportEventModel($expectedEventData);
        $eventObserver = $this->getObserverMock($productId);
        $this->observer->catalogProductView($eventObserver);
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
        $this->reportEventMock->expects($this->any())
            ->method('setData')
            ->with($expectedEventData)
            ->willReturnSelf();

        $this->reportEventMock->expects($this->any())
            ->method('save')
            ->willReturnSelf();
    }

    /**
     * @param int $productId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getObserverMock($productId)
    {
        $eventObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')->disableOriginalConstructor()
            ->getMock();
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')->disableOriginalConstructor()
            ->setMethods(['getProduct'])->getMock();
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $eventMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productMock);
        $eventObserverMock->expects($this->any())
            ->method('getEvent')
            ->willReturn($eventMock);

        return $eventObserverMock;
    }
}
