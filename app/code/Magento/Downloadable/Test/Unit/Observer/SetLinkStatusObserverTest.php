<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Observer;

use Magento\Downloadable\Observer\SetLinkStatusObserver;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection as LinkItemCollection;
use Magento\Downloadable\Model\Product\Type as DownloadableProductType;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetLinkStatusObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order */
    private $orderMock;

    /** @var SetLinkStatusObserver */
    private $setLinkStatusObserver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Config
     */
    private $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | CollectionFactory
     */
    private $itemsFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\DataObject
     */
    private $resultMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\DataObject
     */
    private $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event
     */
    private $eventMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event\Observer
     */
    private $observerMock;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSetFlag', 'getValue'])
            ->getMock();

        $this->itemsFactory = $this->getMockBuilder(
            \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['setIsAllowed'])
            ->getMock();

        $this->storeMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getResult', 'getQuote', 'getOrder'])
            ->getMock();

        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getStoreId', 'getState', 'isCanceled', 'getAllItems'])
            ->getMock();

        $this->observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->setLinkStatusObserver = (new ObjectManagerHelper($this))->getObject(
            \Magento\Downloadable\Observer\SetLinkStatusObserver::class,
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
                'orderState' => \Magento\Sales\Model\Order::STATE_HOLDED,
                'mapping' => [
                    \Magento\Sales\Model\Order::STATE_HOLDED => 'pending',
                    \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT => 'payment_pending',
                    \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW => 'payment_review'

                ],
            ],
            [
                'orderState' => \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                'mapping' => [
                    \Magento\Sales\Model\Order::STATE_HOLDED => 'pending',
                    \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT => 'pending_payment',
                    \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW => 'payment_review'

                ],
            ],
            [
                'orderState' => \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW,
                'mapping' => [
                    \Magento\Sales\Model\Order::STATE_HOLDED => 'pending',
                    \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT => 'payment_pending',
                    \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW => 'payment_review'

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
            ->will($this->returnValue($this->eventMock));

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
                    $this->createOrderItem(3, \Magento\Sales\Model\Order\Item::STATUS_PENDING, null),
                    $this->createOrderItem(4, \Magento\Sales\Model\Order\Item::STATUS_PENDING, null, null),
                    $this->createOrderItem(5, \Magento\Sales\Model\Order\Item::STATUS_PENDING, null),
                ]
            );

        $this->itemsFactory->expects($this->once())
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
        $this->assertInstanceOf(\Magento\Downloadable\Observer\SetLinkStatusObserver::class, $result);
    }

    public function testSetLinkStatusClosed()
    {
        $orderState = \Magento\Sales\Model\Order::STATE_CLOSED;

        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventMock));

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
                    $this->createOrderItem(3, \Magento\Sales\Model\Order\Item::STATUS_CANCELED, null),
                    $this->createOrderItem(4, \Magento\Sales\Model\Order\Item::STATUS_REFUNDED, null, null),
                    $this->createOrderItem(5, \Magento\Sales\Model\Order\Item::STATUS_REFUNDED, null),
                ]
            );

        $this->itemsFactory->expects($this->once())
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
        $this->assertInstanceOf(\Magento\Downloadable\Observer\SetLinkStatusObserver::class, $result);
    }

    public function testSetLinkStatusInvoiced()
    {
        $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(\Magento\Downloadable\Model\Link\Purchased\Item::XML_PATH_ORDER_ITEM_STATUS),
                $this->equalTo(ScopeInterface::SCOPE_STORE),
                $this->equalTo(1)
            )
            ->willReturn(\Magento\Sales\Model\Order\Item::STATUS_PENDING);

        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventMock));

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
                    $this->createOrderItem(3, \Magento\Sales\Model\Order\Item::STATUS_INVOICED, null),
                    $this->createOrderItem(4, \Magento\Sales\Model\Order\Item::STATUS_PENDING, null, null),
                    $this->createOrderItem(5, \Magento\Sales\Model\Order\Item::STATUS_PENDING, null),
                    $this->createOrderItem(6, \Magento\Sales\Model\Order\Item::STATUS_REFUNDED, null),
                    $this->createOrderItem(7, \Magento\Sales\Model\Order\Item::STATUS_BACKORDERED, null),
                ]
            );

        $this->itemsFactory->expects($this->once())
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
        $this->assertInstanceOf(\Magento\Downloadable\Observer\SetLinkStatusObserver::class, $result);
    }

    public function testSetLinkStatusEmptyOrder()
    {
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventMock));

        $this->eventMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $result = $this->setLinkStatusObserver->execute($this->observerMock);
        $this->assertInstanceOf(\Magento\Downloadable\Observer\SetLinkStatusObserver::class, $result);
    }

    /**
     * @param $id
     * @param int $statusId
     * @param string $productType
     * @param string $realProductType
     * @return \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createOrderItem(
        $id,
        $statusId = \Magento\Sales\Model\Order\Item::STATUS_PENDING,
        $productType = DownloadableProductType::TYPE_DOWNLOADABLE,
        $realProductType = DownloadableProductType::TYPE_DOWNLOADABLE
    ) {
        $item = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getProductType', 'getRealProductType', 'getStatusId'])
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

        return $item;
    }

    /**
     * @param array $expectedOrderItemIds
     * @param array $items
     * @return LinkItemCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createLinkItemCollection(array $expectedOrderItemIds, array $items)
    {
        $linkItemCollection = $this->getMockBuilder(
            \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter'])
            ->getMock();
        $linkItemCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with($this->equalTo('order_item_id'), $this->equalTo(['in' => $expectedOrderItemIds]))
            ->willReturn($items);

        return $linkItemCollection;
    }

    /**
     * @param $status
     * @param $orderItemId
     * @param bool $isSaved
     * @param null|string $expectedStatus
     * @return \Magento\Downloadable\Model\Link\Purchased\Item|\PHPUnit_Framework_MockObject_MockObject
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
            $linkItem->expects($this->once())
                ->method('setStatus')
                ->with($this->equalTo($expectedStatus))
                ->willReturnSelf();
            $linkItem->expects($this->once())
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
