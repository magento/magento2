<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Observer;

use Magento\Downloadable\Model\Product\Type as DownloadableProductType;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection as LinkItemCollection;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Downloadable\Observer\SetLinkStatusObserver;
use Magento\Framework\App\Config;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
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
        $this->scopeConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSetFlag', 'getValue'])
            ->getMock();

        $this->itemsFactory = $this->getMockBuilder(
            CollectionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setIsAllowed'])
            ->getMock();

        $this->storeMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getResult', 'getQuote', 'getOrder'])
            ->getMock();

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getStoreId', 'getState', 'isCanceled', 'getAllItems'])
            ->getMock();

        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

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
    public function setLinkStatusPendingDataProvider()
    {
        return [
            [
                'orderState' => Order::STATE_HOLDED,
                'mapping' => [
                    Order::STATE_HOLDED => 'pending',
                    Order::STATE_PENDING_PAYMENT => 'payment_pending',
                    Order::STATE_PAYMENT_REVIEW => 'payment_review'

                ],
            ],
            [
                'orderState' => Order::STATE_PENDING_PAYMENT,
                'mapping' => [
                    Order::STATE_HOLDED => 'pending',
                    Order::STATE_PENDING_PAYMENT => 'pending_payment',
                    Order::STATE_PAYMENT_REVIEW => 'payment_review'

                ],
            ],
            [
                'orderState' => Order::STATE_PAYMENT_REVIEW,
                'mapping' => [
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
     * @dataProvider setLinkStatusPendingDataProvider
     */
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

        $this->itemsFactory->expects($this->any())
            ->method('create')
            ->willReturn(
                $this->createLinkItemCollection(
                    [1, 2, 3, 5],
                    [
                        $this->createLinkItem('available', 1, true, $orderStateMapping[$orderState]),
                        $this->createLinkItem('pending_payment', 2, true, $orderStateMapping[$orderState]),
                        $this->createLinkItem('pending_review', 3, true, $orderStateMapping[$orderState]),
                        $this->createLinkItem('pending', 5, true, $orderStateMapping[$orderState]),
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

        $this->itemsFactory->expects($this->any())
            ->method('create')
            ->willReturn(
                $this->createLinkItemCollection(
                    [1, 2, 3, 5],
                    [
                        $this->createLinkItem('available', 1, true, 'available'),
                        $this->createLinkItem('pending_payment', 2, true, 'available'),
                        $this->createLinkItem('pending_review', 3, true, 'expired'),
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

        $this->itemsFactory->expects($this->any())
            ->method('create')
            ->willReturn(
                $this->createLinkItemCollection(
                    [1, 2, 3, 5, 7],
                    [
                        $this->createLinkItem('available', 1, true, 'available'),
                        $this->createLinkItem('pending_payment', 2, true, 'available'),
                        $this->createLinkItem('pending_review', 3, true, 'available'),
                        $this->createLinkItem('pending_review', 5, true, 'available'),
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

        $this->orderMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn(
                [
                    $this->createRefundOrderItem(2, 2, 2),
                    $this->createRefundOrderItem(3, 2, 1),
                    $this->createRefundOrderItem(4, 3, 3),
                ]
            );

        $this->itemsFactory->expects($this->any())
            ->method('create')
            ->willReturn(
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
     * @return \Magento\Sales\Model\Order\Item|MockObject
     */
    private function createRefundOrderItem(
        $id,
        $qtyOrdered,
        $qtyRefunded,
        $productType = DownloadableProductType::TYPE_DOWNLOADABLE,
        $realProductType = DownloadableProductType::TYPE_DOWNLOADABLE
    ) {
        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getId',
                'getQtyOrdered',
                'getQtyRefunded',
                'getProductType',
                'getRealProductType'
            ])->getMock();
        $item->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $item->expects($this->any())
            ->method('getQtyOrdered')
            ->willReturn($qtyOrdered);
        $item->expects($this->any())
            ->method('getQtyRefunded')
            ->willReturn($qtyRefunded);
        $item->expects($this->any())
            ->method('getProductType')
            ->willReturn($productType);
        $item->expects($this->any())
            ->method('getRealProductType')
            ->willReturn($realProductType);

        return $item;
    }

    /**
     * @param array $expectedOrderItemIds
     * @param array $items
     * @return LinkItemCollection|MockObject
     */
    private function createLinkItemToExpireCollection(array $expectedOrderItemIds, array $items)
    {
        $linkItemCollection = $this->getMockBuilder(
            \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter'])
            ->getMock();
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
     * @return \Magento\Sales\Model\Order\Item|MockObject
     */
    private function createOrderItem(
        $id,
        $statusId = Item::STATUS_PENDING,
        $productType = DownloadableProductType::TYPE_DOWNLOADABLE,
        $realProductType = DownloadableProductType::TYPE_DOWNLOADABLE
    ) {
        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getProductType', 'getRealProductType', 'getStatusId', 'getQtyOrdered'])
            ->getMock();
        $item->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $item->expects($this->any())
            ->method('getProductType')
            ->willReturn($productType);
        $item->expects($this->any())
            ->method('getRealProductType')
            ->willReturn($realProductType);
        $item->expects($this->any())
            ->method('getStatusId')
            ->willReturn($statusId);
        $item->expects($this->any())
            ->method('getQtyOrdered')
            ->willReturn(1);

        return $item;
    }

    /**
     * @param array $expectedOrderItemIds
     * @param array $items
     * @return LinkItemCollection|MockObject
     */
    private function createLinkItemCollection(array $expectedOrderItemIds, array $items)
    {
        $linkItemCollection = $this->getMockBuilder(
            \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter'])
            ->getMock();
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
     */
    private function createLinkItem($status, $orderItemId, $isSaved = false, $expectedStatus = null)
    {
        $linkItem = $this->getMockBuilder(\Magento\Downloadable\Model\Link\Purchased\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus', 'getOrderItemId', 'setStatus', 'save', 'setNumberOfDownloadsBought'])
            ->getMock();
        $linkItem->expects($this->any())
            ->method('getStatus')
            ->willReturn($status);
        if ($isSaved) {
            $linkItem->expects($this->any())
                ->method('setStatus')
                ->with($expectedStatus)
                ->willReturnSelf();
            $linkItem->expects($this->any())
                ->method('save')
                ->willReturnSelf();
        }

        $linkItem->expects($this->any())
            ->method('setNumberOfDownloadsBought')
            ->willReturnSelf();

        $linkItem->expects($this->any())
            ->method('getOrderItemId')
            ->willReturn($orderItemId);

        return $linkItem;
    }
}
