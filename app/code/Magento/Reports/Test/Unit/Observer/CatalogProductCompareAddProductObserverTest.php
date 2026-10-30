<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\Event as FrameworkEvent;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\Event;
use Magento\Reports\Model\EventFactory;
use Magento\Reports\Model\Product\Index\Compared;
use Magento\Reports\Model\Product\Index\ComparedFactory;
use Magento\Reports\Model\ReportStatus;
use Magento\Reports\Observer\CatalogProductCompareAddProductObserver;
use Magento\Reports\Observer\EventSaver;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CatalogProductCompareAddProductObserverTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var CatalogProductCompareAddProductObserver
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
        $storeManager = $this->createMock(StoreManagerInterface::class);
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
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->productCompFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->productCompModelMock);

        $this->eventSaverMock = $this->getMockBuilder(EventSaver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $this->reportStatusMock = $this->getMockBuilder(ReportStatus::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isReportEnabled'])
            ->getMock();

        $this->observer = $objectManager->getObject(
            CatalogProductCompareAddProductObserver::class,
            [
                'productCompFactory' => $this->productCompFactoryMock,
                'customerSession' => $this->customerSessionMock,
                'customerVisitor' => $this->customerVisitorMock,
                'eventSaver' => $this->eventSaverMock,
                'reportStatus' => $this->reportStatusMock
            ]
        );
    }

    /**
     * @param bool $isLoggedIn
     * @param string $userKey
     * @param int $userId
     * @return void
     */
    #[DataProvider('catalogProductCompareAddProductDataProvider')]
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

        $this->reportStatusMock->expects($this->once())->method('isReportEnabled')->willReturn(true);
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
    public static function catalogProductCompareAddProductDataProvider()
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
     * @return MockObject
     */
    protected function getObserverMock($productId)
    {
        $eventObserverMock = $this->createMock(Observer::class);
        $eventMock = $this->createPartialMockWithReflection(
            FrameworkEvent::class,
            ['getProduct']
        );
        $productMock = $this->createMock(Product::class);

        $productMock->expects($this->any())->method('getId')->willReturn($productId);

        $eventMock->expects($this->any())->method('getProduct')->willReturn($productMock);

        $eventObserverMock->expects($this->any())->method('getEvent')->willReturn($eventMock);

        return $eventObserverMock;
    }
}
