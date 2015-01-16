<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Sales\Model\Resource\Order\Status\History\CollectionFactory as HistoryCollectionFactory;

/**
 * Test class for \Magento\Sales\Model\Order
 */
class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemCollectionFactoryMock;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\Event\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var string
     */
    protected $incrementId;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Item | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $item;

    /**
     * @var HistoryCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyCollectionFactoryMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->paymentCollectionFactoryMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Payment\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->orderItemCollectionFactoryMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Item\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->historyCollectionFactoryMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Status\History\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->item = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Item',
            ['isDeleted', 'getQtyToInvoice', 'getParentItemId', 'getQuoteItemId'],
            [],
            '',
            false
        );
        $collection = $this->getMock('Magento\Sales\Model\Resource\Order\Item\Collection', [], [], '', false);
        $collection->expects($this->any())
            ->method('setOrderFilter')
            ->willReturnSelf();
        $collection->expects($this->any())
            ->method('getItems')
            ->willReturn([$this->item]);
        $this->orderItemCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($collection);

        $this->incrementId = '#00000001';
        $this->eventManager = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);
        $context = $this->getMock('Magento\Framework\Model\Context', ['getEventDispatcher'], [], '', false);
        $context->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManager);

        $this->order = $helper->getObject(
            'Magento\Sales\Model\Order',
            [
                'paymentCollectionFactory' => $this->paymentCollectionFactoryMock,
                'orderItemCollectionFactory' => $this->orderItemCollectionFactoryMock,
                'data' => ['increment_id' => $this->incrementId],
                'context' => $context,
                'historyCollectionFactory' => $this->historyCollectionFactoryMock
            ]
        );
    }

    /**
     * @param int|null $gettingQuoteItemId
     * @param int|null $quoteItemId
     * @param string|null $result
     *
     * @dataProvider dataProviderGetItemByQuoteItemId
     */
    public function testGetItemByQuoteItemId($gettingQuoteItemId, $quoteItemId, $result)
    {
        $this->item->expects($this->any())
            ->method('getQuoteItemId')
            ->willReturn($gettingQuoteItemId);

        if ($result !== null) {
            $result = $this->item;
        }

        $this->assertEquals($result, $this->order->getItemByQuoteItemId($quoteItemId));
    }

    /**
     * @return array
     */
    public function dataProviderGetItemByQuoteItemId()
    {
        return [
            [10, 10, 'replace-me'],
            [10, 88, null],
            [88, 10, null],
        ];
    }

    /**
     * @param bool $isDeleted
     * @param int|null $parentItemId
     * @param array $result
     *
     * @dataProvider dataProviderGetAllVisibleItems
     */
    public function testGetAllVisibleItems($isDeleted, $parentItemId, array $result)
    {
        $this->item->expects($this->once())
            ->method('isDeleted')
            ->willReturn($isDeleted);

        $this->item->expects($this->any())
            ->method('getParentItemId')
            ->willReturn($parentItemId);

        if (!empty($result)) {
            $result = [$this->item];
        }

        $this->assertEquals($result, $this->order->getAllVisibleItems());
    }

    /**
     * @return array
     */
    public function dataProviderGetAllVisibleItems()
    {
        return [
            [false, null, ['replace-me']],
            [true, null, []],
            [true, 10, []],
            [false, 10, []],
            [true, null, []],
        ];
    }

    public function testCanCancelCanUnhold()
    {
        $this->order->setActionFlag(\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD, true);
        $this->order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
        $this->assertFalse($this->order->canCancel());
    }

    public function testCanCancelIsPaymentReview()
    {
        $this->order->setActionFlag(\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD, false);
        $this->order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
        $this->assertFalse($this->order->canCancel());
    }

    public function testCanEditIfHasInvoices()
    {
        $invoiceCollection = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Invoice\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['count'])
            ->getMock();

        $invoiceCollection->expects($this->once())
            ->method('count')
            ->willReturn(2);

        $this->order->setInvoiceCollection($invoiceCollection);
        $this->order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);

        $this->assertFalse($this->order->canEdit());
    }

    public function testCanCancelCanReviewPayment()
    {
        $paymentMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Payment')
            ->disableOriginalConstructor()
            ->setMethods(['isDeleted', 'canReviewPayment', 'canFetchTransactionInfo', '__wakeUp'])
            ->getMock();
        $paymentMock->expects($this->any())
            ->method('canReviewPayment')
            ->will($this->returnValue(false));
        $paymentMock->expects($this->any())
            ->method('canFetchTransactionInfo')
            ->will($this->returnValue(true));
        $this->preparePaymentMock($paymentMock);
        $this->order->setActionFlag(\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD, false);
        $this->order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
        $this->assertFalse($this->order->canCancel());
    }

    public function testCanCancelAllInvoiced()
    {
        $paymentMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Payment')
            ->disableOriginalConstructor()
            ->setMethods(['isDeleted', 'canReviewPayment', 'canFetchTransactionInfo', '__wakeUp'])
            ->getMock();
        $paymentMock->expects($this->any())
            ->method('canReviewPayment')
            ->will($this->returnValue(false));
        $paymentMock->expects($this->any())
            ->method('canFetchTransactionInfo')
            ->will($this->returnValue(false));
        $collectionMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Item\Collection',
            ['getItems', 'setOrderFilter'],
            [],
            '',
            false
        );
        $this->orderItemCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->any())
            ->method('setOrderFilter')
            ->willReturnSelf();
        $this->preparePaymentMock($paymentMock);

        $this->prepareItemMock(0);

        $this->order->setActionFlag(\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD, false);
        $this->order->setState(\Magento\Sales\Model\Order::STATE_NEW);

        $this->item->expects($this->any())
            ->method('isDeleted')
            ->willReturn(false);
        $this->item->expects($this->any())
            ->method('getQtyToInvoice')
            ->willReturn(0);

        $this->assertFalse($this->order->canCancel());
    }

    public function testCanCancelState()
    {
        $paymentMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Payment')
            ->disableOriginalConstructor()
            ->setMethods(['isDeleted', 'canReviewPayment', 'canFetchTransactionInfo', '__wakeUp'])
            ->getMock();
        $paymentMock->expects($this->any())
            ->method('canReviewPayment')
            ->will($this->returnValue(false));
        $paymentMock->expects($this->any())
            ->method('canFetchTransactionInfo')
            ->will($this->returnValue(false));

        $this->preparePaymentMock($paymentMock);

        $this->prepareItemMock(1);
        $this->order->setActionFlag(\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD, false);
        $this->order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
        $this->assertFalse($this->order->canCancel());
    }

    /**
     * @param bool $cancelActionFlag
     * @dataProvider dataProviderActionFlag
     */
    public function testCanCancelActionFlag($cancelActionFlag)
    {
        $paymentMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Payment')
            ->disableOriginalConstructor()
            ->setMethods(['isDeleted', 'canReviewPayment', 'canFetchTransactionInfo', '__wakeUp'])
            ->getMock();
        $paymentMock->expects($this->any())
            ->method('canReviewPayment')
            ->will($this->returnValue(false));
        $paymentMock->expects($this->any())
            ->method('canFetchTransactionInfo')
            ->will($this->returnValue(false));

        $this->preparePaymentMock($paymentMock);

        $this->prepareItemMock(1);

        $actionFlags = [
            \Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD => false,
            \Magento\Sales\Model\Order::ACTION_FLAG_CANCEL => $cancelActionFlag,
        ];
        foreach ($actionFlags as $action => $flag) {
            $this->order->setActionFlag($action, $flag);
        }
        $this->order->setData('state', \Magento\Sales\Model\Order::STATE_NEW);

        $this->item->expects($this->any())
            ->method('isDeleted')
            ->willReturn(false);
        $this->item->expects($this->any())
            ->method('getQtyToInvoice')
            ->willReturn(42);

        $this->assertEquals($cancelActionFlag, $this->order->canCancel());
    }

    /**
     * @param array $actionFlags
     * @param string $orderState
     * @dataProvider canVoidPaymentDataProvider
     */
    public function testCanVoidPayment($actionFlags, $orderState)
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var Order $order */
        $order = $helper->getObject('Magento\Sales\Model\Order');
        foreach ($actionFlags as $action => $flag) {
            $order->setActionFlag($action, $flag);
        }
        $order->setData('state', $orderState);
        $payment = $this->_prepareOrderPayment($order);
        $canVoidOrder = true;
        if ($orderState == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW) {
            $canVoidOrder = false;
        }
        if ($orderState == \Magento\Sales\Model\Order::STATE_HOLDED && (!isset(
                    $actionFlags[\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD]
                ) || $actionFlags[\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD] !== false)
        ) {
            $canVoidOrder = false;
        }

        $expected = false;
        if ($canVoidOrder) {
            $expected = 'some value';
            $payment->expects(
                $this->any()
            )->method(
                'canVoid'
            )->with(
                new \PHPUnit_Framework_Constraint_IsIdentical($payment)
            )->will(
                $this->returnValue($expected)
            );
        } else {
            $payment->expects($this->never())->method('canVoid');
        }
        $this->assertEquals($expected, $order->canVoidPayment());
    }

    /**
     * @param $paymentMock
     */
    protected function preparePaymentMock($paymentMock)
    {
        $iterator = new \ArrayIterator([$paymentMock]);

        $collectionMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Payment\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['setOrderFilter', 'getIterator'])
            ->getMock();
        $collectionMock->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue($iterator));
        $collectionMock->expects($this->any())
            ->method('setOrderFilter')
            ->will($this->returnSelf());

        $this->paymentCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($collectionMock));
    }

    /**
     * Prepare payment for the order
     *
     * @param \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject $order
     * @param array $mockedMethods
     * @return \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _prepareOrderPayment($order, $mockedMethods = [])
    {
        $payment = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')->disableOriginalConstructor()->getMock();
        foreach ($mockedMethods as $method => $value) {
            $payment->expects($this->any())->method($method)->will($this->returnValue($value));
        }
        $payment->expects($this->any())->method('isDeleted')->will($this->returnValue(false));

        $order->setData(\Magento\Sales\Api\Data\OrderInterface::PAYMENTS, [$payment]);

        return $payment;
    }

    /**
     * Get action flags
     *
     */
    protected function _getActionFlagsValues()
    {
        return [
            [],
            [
                \Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD => false,
                \Magento\Sales\Model\Order::ACTION_FLAG_CANCEL => false
            ],
            [
                \Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD => false,
                \Magento\Sales\Model\Order::ACTION_FLAG_CANCEL => true
            ]
        ];
    }

    /**
     * Get order statuses
     *
     * @return array
     */
    protected function _getOrderStatuses()
    {
        return [
            \Magento\Sales\Model\Order::STATE_HOLDED,
            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW,
            \Magento\Sales\Model\Order::STATE_CANCELED,
            \Magento\Sales\Model\Order::STATE_COMPLETE,
            \Magento\Sales\Model\Order::STATE_CLOSED,
            \Magento\Sales\Model\Order::STATE_PROCESSING
        ];
    }

    /**
     * @param int $qtyInvoiced
     * @return void
     */
    protected function prepareItemMock($qtyInvoiced)
    {
        $itemMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods(['isDeleted', 'filterByTypes', 'filterByParent', 'getQtyToInvoice', '__wakeUp'])
            ->getMock();

        $itemMock->expects($this->any())
            ->method('getQtyToInvoice')
            ->will($this->returnValue($qtyInvoiced));

        $iterator = new \ArrayIterator([$itemMock]);

        $itemCollectionMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Item\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['setOrderFilter', 'getIterator', 'getItems'])
            ->getMock();
        $itemCollectionMock->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue($iterator));
        $itemCollectionMock->expects($this->any())
            ->method('setOrderFilter')
            ->will($this->returnSelf());

        $this->orderItemCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($itemCollectionMock));
    }

    public function canVoidPaymentDataProvider()
    {
        $data = [];
        foreach ($this->_getActionFlagsValues() as $actionFlags) {
            foreach ($this->_getOrderStatuses() as $status) {
                $data[] = [$actionFlags, $status];
            }
        }
        return $data;
    }

    public function dataProviderActionFlag()
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * test method getIncrementId()
     */
    public function testGetIncrementId()
    {
        $this->assertEquals($this->incrementId, $this->order->getIncrementId());
    }

    public function testGetEntityType()
    {
        $this->assertEquals('order', $this->order->getEntityType());
    }

    /**
     * Run test getStatusHistories method
     *
     * @return void
     */
    public function testGetStatusHistories()
    {
        $itemMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\Data\OrderStatusHistoryInterface',
            [],
            '',
            false,
            true,
            true,
            ['setOrder']
        );
        $dbMock = $this->getMock(
            'Magento\Framework\Data\Collection\Db',
            ['setOrder'],
            [],
            '',
            false
        );
        $collectionMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Status\History\Collection',
            [
                'setOrderFilter',
                'setOrder',
                'getItems',
                'getIterator',
                'toOptionArray',
                'count',
                'load'
            ],
            [],
            '',
            false
        );

        $collectionItems = [$itemMock];

        $collectionMock->expects($this->once())
            ->method('setOrderFilter')
            ->with($this->order)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setOrder')
            ->with('created_at', 'desc')
            ->willReturn($dbMock);
        $dbMock->expects($this->once())
            ->method('setOrder')
            ->with('entity_id', 'desc')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn($collectionItems);

        $this->historyCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        for ($i = 10; --$i;) {
            $this->assertEquals($collectionItems, $this->order->getStatusHistories());
        }
    }
}
