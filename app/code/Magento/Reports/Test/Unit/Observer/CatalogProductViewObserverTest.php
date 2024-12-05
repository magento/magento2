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
use Magento\Reports\Model\ReportStatus;
use Magento\Reports\Observer\CatalogProductViewObserver;
use Magento\Reports\Observer\EventSaver;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CatalogProductViewObserverTest extends TestCase
{
    /**
     * @var CatalogProductViewObserver
     */
    protected $observer;

    /**
     * @var EventSaver|MockObject
     */
    protected $eventSaverMock;

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
     * @var Store|MockObject
     */
    protected $storeMock;

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
     * @var ReportStatus|MockObject
     */
    private $reportStatusMock;

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
        )
            ->onlyMethods(['create'])
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

        /** @var StoreManagerInterface|MockObject $storeManager */
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

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

        $this->productCompFactoryMock = $this->getMockBuilder(
            ComparedFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->eventSaverMock = $this->getMockBuilder(EventSaver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $this->reportStatusMock = $this->getMockBuilder(ReportStatus::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isReportEnabled'])
            ->getMock();

        $this->observer = $objectManager->getObject(
            CatalogProductViewObserver::class,
            [
                'storeManager' => $storeManager,
                'productIndxFactory' => $this->productIndexFactoryMock,
                'customerSession' => $this->customerSessionMock,
                'customerVisitor' => $this->customerVisitorMock,
                'eventSaver' => $this->eventSaverMock,
                'reportStatus' => $this->reportStatusMock
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
            'event_type_id' => Event::EVENT_PRODUCT_VIEW,
            'object_id' => $productId,
            'subject_id' => $customerId,
            'subtype' => 0,
            'store_id' => $storeId,
        ];

        $this->reportStatusMock->expects($this->once())->method('isReportEnabled')->willReturn(true);
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
            'event_type_id' => Event::EVENT_PRODUCT_VIEW,
            'object_id' => $productId,
            'subject_id' => $visitorId,
            'subtype' => 1,
            'store_id' => $storeId,
        ];

        $this->reportStatusMock->expects($this->once())->method('isReportEnabled')->willReturn(true);
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
