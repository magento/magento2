<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Unit\Model;

use Magento\Downloadable\Model\Observer;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Product\Type as DownloadableProductType;
use Magento\Downloadable\Model\Resource\Link\Purchased\Item\Collection as LinkItemCollection;
use Magento\Downloadable\Model\Resource\Link\Purchased\Item\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order */
    private $orderMock;

    /** @var Observer */
    private $observer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Config
     */
    private $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    private $purchasedFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Downloadable\Model\Link\Purchased\ItemFactory
     */
    private $itemFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | CollectionFactory
     */
    private $itemsFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Object\Copy
     */
    private $objectCopyService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Object
     */
    private $resultMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Object
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
    public function setUp()
    {
        $this->scopeConfig = $this->getMockBuilder('\Magento\Framework\App\Config')
            ->disableOriginalConstructor()
            ->setMethods(['isSetFlag', 'getValue'])
            ->getMock();

        $this->purchasedFactory = $this->getMockBuilder('\Magento\Downloadable\Model\Link\PurchasedFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFactory = $this->getMockBuilder('\Magento\Catalog\Model\ProductFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemFactory = $this->getMockBuilder('\Magento\Downloadable\Model\Link\Purchased\ItemFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSession = $this->getMockBuilder('\Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemsFactory = $this->getMockBuilder(
            '\Magento\Downloadable\Model\Resource\Link\Purchased\Item\CollectionFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectCopyService = $this->getMockBuilder('\Magento\Framework\Object\Copy')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockBuilder('\Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(['setIsAllowed'])
            ->getMock();

        $this->storeMock = $this->getMockBuilder('\Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder('\Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getResult', 'getQuote', 'getOrder'])
            ->getMock();

        $this->orderMock = $this->getMockBuilder('\Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getStoreId', 'getState', 'isCanceled', 'getAllItems'])
            ->getMock();

        $this->observerMock = $this->getMockBuilder('\Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->observer = (new ObjectManagerHelper($this))->getObject(
            '\Magento\Downloadable\Model\Observer',
            [
                'scopeConfig' => $this->scopeConfig,
                'purchasedFactory' => $this->purchasedFactory,
                'productFactory' => $this->productFactory,
                'itemFactory' => $this->itemFactory,
                'checkoutSession' => $this->checkoutSession,
                'itemsFactory' => $this->itemsFactory,
                'objectCopyService' => $this->objectCopyService
            ]
        );
    }

    /**
     *
     * @dataProvider dataProviderForTestisAllowedGuestCheckoutConfigSetToTrue
     *
     * @param $productType
     * @param $isAllowed
     */
    public function testIsAllowedGuestCheckoutConfigSetToTrue($productType, $isAllowed)
    {
        $this->resultMock->expects($this->at(0))
            ->method('setIsAllowed')
            ->with(true);

        if ($isAllowed) {
            $this->resultMock->expects($this->at(1))
                ->method('setIsAllowed')
                ->with(false);
        }

        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId'])
            ->getMock();

        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);

        $item = $this->getMockBuilder('\Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();

        $item->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);

        $quote = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getAllItems'])
            ->getMock();

        $quote->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$item]);

        $this->eventMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->eventMock->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($this->resultMock));

        $this->eventMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($quote));

        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(Observer::XML_PATH_DISABLE_GUEST_CHECKOUT, ScopeInterface::SCOPE_STORE, $this->storeMock)
            ->willReturn(true);

        $this->observerMock->expects($this->exactly(3))
            ->method('getEvent')
            ->will($this->returnValue($this->eventMock));

        $this->assertInstanceOf(
            '\Magento\Downloadable\Model\Observer',
            $this->observer->isAllowedGuestCheckout($this->observerMock)
        );
    }

    /**
     * @return array
     */
    public function dataProviderForTestisAllowedGuestCheckoutConfigSetToTrue()
    {
        return [
            1 => [Type::TYPE_DOWNLOADABLE, true],
            2 => ['unknown', false],
        ];
    }

    /**
     *
     */
    public function testIsAllowedGuestCheckoutConfigSetToFalse()
    {
        $this->resultMock->expects($this->once())
            ->method('setIsAllowed')
            ->with(true);

        $this->eventMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->eventMock->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($this->resultMock));

        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(Observer::XML_PATH_DISABLE_GUEST_CHECKOUT, ScopeInterface::SCOPE_STORE, $this->storeMock)
            ->willReturn(false);

        $this->observerMock->expects($this->exactly(2))
            ->method('getEvent')
            ->will($this->returnValue($this->eventMock));

        $this->assertInstanceOf(
            '\Magento\Downloadable\Model\Observer',
            $this->observer->isAllowedGuestCheckout($this->observerMock)
        );
    }

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

        $result = $this->observer->setLinkStatus($this->observerMock);
        $this->assertInstanceOf('\Magento\Downloadable\Model\Observer', $result);
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

        $result = $this->observer->setLinkStatus($this->observerMock);
        $this->assertInstanceOf('\Magento\Downloadable\Model\Observer', $result);
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

        $result = $this->observer->setLinkStatus($this->observerMock);
        $this->assertInstanceOf('\Magento\Downloadable\Model\Observer', $result);
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

        $result = $this->observer->setLinkStatus($this->observerMock);
        $this->assertInstanceOf('\Magento\Downloadable\Model\Observer', $result);
    }

    public function testSaveDownloadableOrderItem()
    {
        $itemId = 100;
        $itemMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->atLeastOnce())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);
        $itemMock->expects($this->any())
            ->method('getProductType')
            ->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);

        $this->orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(10500);

        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);
        $productType = $this->getMockBuilder('\Magento\Downloadable\Model\Product\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $product->expects($this->once())
            ->method('setStoreId')
            ->with(10500)
            ->willReturnSelf();
        $product->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->productFactory->expects($this->once())
            ->method('create')
            ->willReturn($product);

        $linkItem = $this->createLinkItem(12, 12, true, 'pending');
        $this->itemFactory->expects($this->once())
            ->method('create')
            ->willReturn($linkItem);

        $productType->expects($this->once())
            ->method('getLinks')
            ->willReturn([123 => $linkItem]);

        $itemMock->expects($this->once())
            ->method('getProductOptionByCode')
            ->willReturn([123]);
        $itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn(null);

        $purchasedLink = $this->getMockBuilder('\Magento\Downloadable\Model\Link\Purchased')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'setLinkSectionTitle', 'save'])
            ->getMock();
        $purchasedLink->expects($this->once())
            ->method('load')
            ->with($itemId, 'order_item_id')
            ->willReturnSelf();
        $purchasedLink->expects($this->once())
            ->method('setLinkSectionTitle')
            ->willReturnSelf();
        $purchasedLink->expects($this->once())
            ->method('save')
            ->willReturnSelf();
        $this->purchasedFactory->expects($this->any())
            ->method('create')
            ->willReturn($purchasedLink);
        $event = new \Magento\Framework\Object(
            [
                'item' => $itemMock,
            ]
        );
        $observer = new \Magento\Framework\Object(
            [
                'event' => $event
            ]
        );
        $this->observer->saveDownloadableOrderItem($observer);
    }

    public function testSaveDownloadableOrderItemNotDownloadableItem()
    {
        $itemId = 100;
        $itemMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);
        $itemMock->expects($this->any())
            ->method('getProductType')
            ->willReturn('simple');
        $itemMock->expects($this->never())
            ->method('getProduct');
        $event = new \Magento\Framework\Object(
            [
                'item' => $itemMock,
            ]
        );
        $observer = new \Magento\Framework\Object(
            [
                'event' => $event
            ]
        );
        $this->observer->saveDownloadableOrderItem($observer);
    }

    public function testSaveDownloadableOrderItemNotSavedOrderItem()
    {
        $itemMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getId')
            ->willReturn(null);
        $event = new \Magento\Framework\Object(
            [
                'item' => $itemMock,
            ]
        );
        $observer = new \Magento\Framework\Object(
            [
                'event' => $event
            ]
        );
        $this->observer->saveDownloadableOrderItem($observer);
    }

    public function testSaveDownloadableOrderItemSavedPurchasedLink()
    {
        $itemId = 100;
        $itemMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);
        $itemMock->expects($this->any())
            ->method('getProductType')
            ->willReturn(DownloadableProductType::TYPE_DOWNLOADABLE);

        $purchasedLink = $this->getMockBuilder('\Magento\Downloadable\Model\Link\Purchased')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'setLinkSectionTitle', 'save', 'getId'])
            ->getMock();
        $purchasedLink->expects($this->once())
            ->method('load')
            ->with($itemId, 'order_item_id')
            ->willReturnSelf();
        $purchasedLink->expects($this->once())
            ->method('getId')
            ->willReturn(123);
        $this->purchasedFactory->expects($this->any())
            ->method('create')
            ->willReturn($purchasedLink);

        $event = new \Magento\Framework\Object(
            [
                'item' => $itemMock,
            ]
        );
        $observer = new \Magento\Framework\Object(
            [
                'event' => $event
            ]
        );
        $this->observer->saveDownloadableOrderItem($observer);
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
        $item = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
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
     * @param $status
     * @param $orderItemId
     * @param bool $isSaved
     * @param null|string $expectedStatus
     * @return \Magento\Downloadable\Model\Link\Purchased\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createLinkItem($status, $orderItemId, $isSaved = false, $expectedStatus = null)
    {
        $linkItem = $this->getMockBuilder('\Magento\Downloadable\Model\Link\Purchased\Item')
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

    /**
     * @param array $expectedOrderItemIds
     * @param array $items
     * @return LinkItemCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createLinkItemCollection(array $expectedOrderItemIds, array $items)
    {
        $linkItemCollection = $this->getMockBuilder(
            '\Magento\Downloadable\Model\Resource\Link\Purchased\Item\Collection'
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
}
