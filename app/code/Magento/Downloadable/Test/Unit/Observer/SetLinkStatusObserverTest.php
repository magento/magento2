<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Observer;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Downloadable\Model\Link\Purchased\Item as DownloadableItem;
use Magento\Downloadable\Model\Product\Type as DownloadableProductType;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection as LinkItemCollection;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Downloadable\Observer\SetLinkStatusObserver;
use Magento\Framework\App\Config;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetLinkStatusObserverTest extends TestCase
{
    use MockCreationTrait;
    /** @var MockObject|Order */
    private $orderMock;

    /** @var SetLinkStatusObserver */
    private $setLinkStatusObserver;

    /**
     * @var MockObject|Config
     */
    private $scopeConfig;

    /**
     * @var MockObject|CollectionFactory
     */
    private $itemsFactory;

    /**
     * @var MockObject|DataObject
     */
    private $resultMock;

    /**
     * @var MockObject|DataObject
     */
    private $storeMock;

    /**
     * @var MockObject|Event
     */
    private $eventMock;

    /**
     * @var MockObject|Observer
     */
    private $observerMock;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->createPartialMock(Config::class, ['isSetFlag', 'getValue']);

        $this->itemsFactory = $this->createPartialMock(CollectionFactory::class, ['create']);

        $this->resultMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['setIsAllowed']
        );

        $this->storeMock = $this->createMock(DataObject::class);

        $this->eventMock = $this->createPartialMockWithReflection(
            Event::class,
            ['getStore', 'getResult', 'getQuote', 'getOrder']
        );

        $this->orderMock = $this->createPartialMock(
            Order::class,
            ['getId', 'getStoreId', 'getState', 'isCanceled', 'getAllItems']
        );

        $this->observerMock = $this->createPartialMock(Observer::class, ['getEvent']);

        $this->setLinkStatusObserver = (new ObjectManagerHelper($this))->getObject(
            SetLinkStatusObserver::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'itemsFactory' => $this->itemsFactory,
            ]
        );
    }

    /**
     * @return array
     */
    public static function setLinkStatusPendingDataProvider()
    {
        return [
            [
                'orderState' => Order::STATE_HOLDED,
                'orderStateMapping' => [
                    Order::STATE_HOLDED => 'pending',
                    Order::STATE_PENDING_PAYMENT => 'payment_pending',
                    Order::STATE_PAYMENT_REVIEW => 'payment_review'

                ],
            ],
            [
                'orderState' => Order::STATE_PENDING_PAYMENT,
                'orderStateMapping' => [
                    Order::STATE_HOLDED => 'pending',
                    Order::STATE_PENDING_PAYMENT => 'pending_payment',
                    Order::STATE_PAYMENT_REVIEW => 'payment_review'

                ],
            ],
            [
                'orderState' => Order::STATE_PAYMENT_REVIEW,
                'orderStateMapping' => [
                    Order::STATE_HOLDED => 'pending',
                    Order::STATE_PENDING_PAYMENT => 'payment_pending',
                    Order::STATE_PAYMENT_REVIEW => 'payment_review'

                ],
            ],
        ];
    }

    /**
     * @param string $orderState
     * @param array $orderStateMapping
     */
    #[DataProvider('setLinkStatusPendingDataProvider')]
    public function testSetLinkStatusPending($orderState, array $orderStateMapping)
    {
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $this->orderMock->expects($this->atLeastOnce())
            ->method('getState')
            ->willReturn($orderState);

        $this->orderMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn(
                [
                    $this->createOrderItem(1),
                    $this->createOrderItem(2),
                    $this->createOrderItem(3, Item::STATUS_PENDING, null),
                    $this->createOrderItem(4, Item::STATUS_PENDING, null, null),
                    $this->createOrderItem(5, Item::STATUS_PENDING, null),
                ]
            );

        $this->itemsFactory->method('create')->willReturn(
            $this->createLinkItemCollection(
                [1, 2, 3, 5],
                [
                        $this->createLinkItem(
                            'available',
                            1,
                            true,
                            $orderStateMapping[$orderState]
                        ),
                        $this->createLinkItem(
                            'pending_payment',
                            2,
                            true,
                            $orderStateMapping[$orderState]
                        ),
                        $this->createLinkItem(
                            'pending_review',
                            3,
                            true,
                            $orderStateMapping[$orderState]
                        ),
                        $this->createLinkItem(
                            'pending',
                            5,
                            true,
                            $orderStateMapping[$orderState]
                        ),
                    ]
            )
        );

        $result = $this->setLinkStatusObserver->execute($this->observerMock);
        $this->assertInstanceOf(SetLinkStatusObserver::class, $result);
    }

    public function testSetLinkStatusClosed()
    {
        $orderState = Order::STATE_CLOSED;

        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $this->orderMock->expects($this->exactly(2))
            ->method('getState')
            ->willReturn($orderState);

        $this->orderMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn(
                [
                    $this->createOrderItem(1),
                    $this->createOrderItem(2),
                    $this->createOrderItem(3, Item::STATUS_CANCELED, null),
                    $this->createOrderItem(4, Item::STATUS_REFUNDED, null, null),
                    $this->createOrderItem(5, Item::STATUS_REFUNDED, null),
                ]
            );

        $this->itemsFactory->method('create')->willReturn(
            $this->createLinkItemCollection(
                [1, 2, 3, 5],
                [
                        $this->createLinkItem('available', 1, true, 'available'),
                        $this->createLinkItem(
                            'pending_payment',
                            2,
                            true,
                            'available'
                        ),
                        $this->createLinkItem(
                            'pending_review',
                            3,
                            true,
                            'expired'
                        ),
                        $this->createLinkItem('pending', 5, true, 'expired'),
                    ]
            )
        );

        $result = $this->setLinkStatusObserver->execute($this->observerMock);
        $this->assertInstanceOf(SetLinkStatusObserver::class, $result);
    }

    public function testSetLinkStatusInvoiced()
    {
        $orderState = Order::STATE_PROCESSING;

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Downloadable\Model\Link\Purchased\Item::XML_PATH_ORDER_ITEM_STATUS,
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn(Item::STATUS_PENDING);

        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $this->orderMock->expects($this->atLeastOnce())
            ->method('getState')
            ->willReturn($orderState);

        $this->orderMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn(
                [
                    $this->createOrderItem(1),
                    $this->createOrderItem(2),
                    $this->createOrderItem(3, Item::STATUS_INVOICED, null),
                    $this->createOrderItem(4, Item::STATUS_PENDING, null, null),
                    $this->createOrderItem(5, Item::STATUS_PENDING, null),
                    $this->createOrderItem(6, Item::STATUS_REFUNDED, null),
                    $this->createOrderItem(7, Item::STATUS_BACKORDERED, null),
                ]
            );

        $this->itemsFactory->method('create')->willReturn(
            $this->createLinkItemCollection(
                [1, 2, 3, 5, 7],
                [
                        $this->createLinkItem(
                            'available',
                            1,
                            true,
                            'available'
                        ),
                        $this->createLinkItem(
                            'pending_payment',
                            2,
                            true,
                            'available'
                        ),
                        $this->createLinkItem(
                            'pending_review',
                            3,
                            true,
                            'available'
                        ),
                        $this->createLinkItem(
                            'pending_review',
                            5,
                            true,
                            'available'
                        ),
                    ]
            )
        );

        $result = $this->setLinkStatusObserver->execute($this->observerMock);
        $this->assertInstanceOf(SetLinkStatusObserver::class, $result);
    }

    public function testSetLinkStatusEmptyOrder()
    {
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $result = $this->setLinkStatusObserver->execute($this->observerMock);
        $this->assertInstanceOf(SetLinkStatusObserver::class, $result);
    }

    public function testSetLinkStatusExpired()
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Downloadable\Model\Link\Purchased\Item::XML_PATH_ORDER_ITEM_STATUS,
                ScopeInterface::SCOPE_STORE,
                1
            )
            ->willReturn(Item::STATUS_PENDING);

        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $this->orderMock->expects($this->atLeastOnce())
            ->method('getState')
            ->willReturn(Order::STATE_PROCESSING);

        $this->orderMock->method('getAllItems')->willReturn(
            [
                    $this->createRefundOrderItem(2, 2, 2),
                    $this->createRefundOrderItem(3, 2, 1),
                    $this->createRefundOrderItem(4, 3, 3),
                ]
        );

        $this->itemsFactory->method('create')->willReturn(
            $this->createLinkItemToExpireCollection(
                [2, 4],
                [
                        $this->createLinkItem(
                            'available',
                            2,
                            true,
                            \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED
                        ),
                        $this->createLinkItem(
                            'pending_payment',
                            4,
                            true,
                            \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED
                        ),
                    ]
            )
        );

        $result = $this->setLinkStatusObserver->execute($this->observerMock);
        $this->assertInstanceOf(SetLinkStatusObserver::class, $result);
    }

    /**
     * @param $id
     * @param int $qtyOrdered
     * @param int $qtyRefunded
     * @param string $productType
     * @param string $realProductType
     * @return Item|MockObject
     */
    private function createRefundOrderItem(
        $id,
        $qtyOrdered,
        $qtyRefunded,
        $productType = DownloadableProductType::TYPE_DOWNLOADABLE,
        $realProductType = DownloadableProductType::TYPE_DOWNLOADABLE
    ) {
        $item = $this->createPartialMock(Item::class, [
                'getId',
                'getQtyOrdered',
                'getQtyRefunded',
                'getProductType',
                'getRealProductType'
            ]);
        $item->method('getId')->willReturn($id);
        $item->method('getQtyOrdered')->willReturn($qtyOrdered);
        $item->method('getQtyRefunded')->willReturn($qtyRefunded);
        $item->method('getProductType')->willReturn($productType);
        $item->method('getRealProductType')->willReturn($realProductType);

        return $item;
    }

    /**
     * @param array $expectedOrderItemIds
     * @param array $items
     * @return LinkItemCollection|MockObject
     */
    private function createLinkItemToExpireCollection(array $expectedOrderItemIds, array $items)
    {
        $linkItemCollection = $this->createPartialMock(
            LinkItemCollection::class,
            ['addFieldToFilter']
        );
        $linkItemCollection->expects($this->any())
            ->method('addFieldToFilter')
            ->with('order_item_id', ['in' => $expectedOrderItemIds])
            ->willReturn($items);

        return $linkItemCollection;
    }

    /**
     * @param $id
     * @param int $statusId
     * @param string $productType
     * @param string $realProductType
     * @return Item|MockObject
     */
    private function createOrderItem(
        $id,
        $statusId = Item::STATUS_PENDING,
        $productType = DownloadableProductType::TYPE_DOWNLOADABLE,
        $realProductType = DownloadableProductType::TYPE_DOWNLOADABLE
    ) {
        $item = $this->createPartialMock(
            Item::class,
            ['getId', 'getProductType', 'getRealProductType', 'getStatusId', 'getQtyOrdered']
        );
        $item->method('getId')->willReturn($id);
        $item->method('getProductType')->willReturn($productType);
        $item->method('getRealProductType')->willReturn($realProductType);
        $item->method('getStatusId')->willReturn($statusId);
        $item->method('getQtyOrdered')->willReturn(1);

        return $item;
    }

    /**
     * @param array $expectedOrderItemIds
     * @param array $items
     * @return LinkItemCollection|MockObject
     */
    private function createLinkItemCollection(array $expectedOrderItemIds, array $items)
    {
        $linkItemCollection = $this->createPartialMock(
            LinkItemCollection::class,
            ['addFieldToFilter']
        );
        $linkItemCollection->expects($this->any())
            ->method('addFieldToFilter')
            ->with('order_item_id', ['in' => $expectedOrderItemIds])
            ->willReturn($items);

        return $linkItemCollection;
    }

    /**
     * @param $status
     * @param $orderItemId
     * @param bool $isSaved
     * @param null|string $expectedStatus
     * @return \Magento\Downloadable\Model\Link\Purchased\Item|MockObject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function createLinkItem($status, $orderItemId, $isSaved = false, $expectedStatus = null)
    {
        // Use parent Item class - all getters/setters work via magic __call() methods
        $linkItem = $this->createPartialMock(DownloadableItem::class, ['save']);

        // Set data directly - getters will work via magic methods
        $linkItem->setData('status', $status);
        $linkItem->setData('order_item_id', $orderItemId);

        if ($isSaved) {
            $linkItem->expects($this->any())
                ->method('save')
                ->willReturnSelf();
        }

        return $linkItem;
    }
}
