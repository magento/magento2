<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory as HistoryCollectionFactory;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test class for \Magento\Sales\Model\Order
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class OrderTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Sales\Model\ResourceModel\Order\Item | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $item;

    /**
     * @var HistoryCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesOrderCollectionFactoryMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesOrderCollectionMock;

    /**
     * @var ProductCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productCollectionFactoryMock;

    /**
     * @var ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeResolver;

    /**
     * @var TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $timezone;

    /**
     * @var OrderItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemRepository;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var MockObject|ScopeConfigInterface $scopeConfigMock
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paymentCollectionFactoryMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory::class,
            ['create']
        );
        $this->orderItemCollectionFactoryMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory::class,
            ['create']
        );
        $this->historyCollectionFactoryMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory::class,
            ['create']
        );
        $this->productCollectionFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create']
        );
        $this->salesOrderCollectionFactoryMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class,
            ['create']
        );
        $this->item = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Item::class,
            [
                'isDeleted',
                'getQtyToInvoice',
                'getParentItemId',
                'getQuoteItemId',
                'getLockedDoInvoice',
                'getProductId',
            ]
        );
        $this->salesOrderCollectionMock = $this->getMockBuilder(
            \Magento\Sales\Model\ResourceModel\Order\Collection::class
        )->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'load', 'getFirstItem'])
            ->getMock();
        $collection = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Item\Collection::class);
        $collection->expects($this->any())->method('setOrderFilter')->willReturnSelf();
        $collection->expects($this->any())->method('getItems')->willReturn([$this->item]);
        $collection->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator([$this->item]));
        $this->orderItemCollectionFactoryMock->expects($this->any())->method('create')->willReturn($collection);

        $this->priceCurrency = $this->getMockForAbstractClass(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['round']
        );
        $this->localeResolver = $this->createMock(ResolverInterface::class);
        $this->timezone = $this->createMock(TimezoneInterface::class);
        $this->incrementId = '#00000001';
        $this->eventManager = $this->createMock(\Magento\Framework\Event\Manager::class);
        $context = $this->createPartialMock(\Magento\Framework\Model\Context::class, ['getEventDispatcher']);
        $context->expects($this->any())->method('getEventDispatcher')->willReturn($this->eventManager);

        $this->itemRepository = $this->getMockBuilder(OrderItemRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->order = $helper->getObject(
            \Magento\Sales\Model\Order::class,
            [
                'paymentCollectionFactory' => $this->paymentCollectionFactoryMock,
                'orderItemCollectionFactory' => $this->orderItemCollectionFactoryMock,
                'data' => ['increment_id' => $this->incrementId],
                'context' => $context,
                'historyCollectionFactory' => $this->historyCollectionFactoryMock,
                'salesOrderCollectionFactory' => $this->salesOrderCollectionFactoryMock,
                'priceCurrency' => $this->priceCurrency,
                'productListFactory' => $this->productCollectionFactoryMock,
                'localeResolver' => $this->localeResolver,
                'timezone' => $this->timezone,
                'itemRepository' => $this->itemRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * Test testGetItems method.
     */
    public function testGetItems()
    {
        $orderItems = [$this->item];

        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')->willReturnSelf();

        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);

        $itemsCollection = $this->getMockBuilder(OrderItemSearchResultInterface::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $itemsCollection->expects($this->once())->method('getItems')->willReturn($orderItems);
        $this->itemRepository->expects($this->once())->method('getList')->willReturn($itemsCollection);

        $this->assertEquals($orderItems, $this->order->getItems());
    }

    /**
     * Prepare order item mock.
     *
     * @param int $orderId
     * @return void
     */
    private function prepareOrderItem(int $orderId = 0)
    {
        $this->order->setData(
            \Magento\Sales\Api\Data\OrderInterface::ITEMS,
            [
                $orderId => $this->item
            ]
        );
    }

    /**
     * Test GetItemById method.
     *
     * @return void
     */
    public function testGetItemById()
    {
        $realOrderItemId = 1;
        $fakeOrderItemId = 2;

        $this->prepareOrderItem($realOrderItemId);

        $this->assertEquals($this->item, $this->order->getItemById($realOrderItemId));
        $this->assertEquals(null, $this->order->getItemById($fakeOrderItemId));
    }

    /**
     * Test GetItemByQuoteItemId method.
     *
     * @param int|null $gettingQuoteItemId
     * @param int|null $quoteItemId
     * @param string|null $result
     *
     * @dataProvider dataProviderGetItemByQuoteItemId
     * @return void
     */
    public function testGetItemByQuoteItemId($gettingQuoteItemId, $quoteItemId, $result)
    {
        $this->prepareOrderItem();

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
     * Test getAllVisibleItems method.
     *
     * @param bool $isDeleted
     * @param int|null $parentItemId
     * @param array $result
     *
     * @dataProvider dataProviderGetAllVisibleItems
     * @return void
     */
    public function testGetAllVisibleItems($isDeleted, $parentItemId, array $result)
    {
        $this->prepareOrderItem();

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

    /**
     * Test CanInvoice method.
     *
     * @return void
     */
    public function testCanInvoice()
    {
        $this->prepareOrderItem();

        $this->item->expects($this->any())
            ->method('getQtyToInvoice')
            ->willReturn(42);
        $this->item->expects($this->any())
            ->method('getLockedDoInvoice')
            ->willReturn(false);

        $this->assertTrue($this->order->canInvoice());
    }

    /**
     * Ensure customer name returned correctly.
     *
     * @dataProvider customerNameProvider
     * @param array $expectedData
     */
    public function testGetCustomerName(array $expectedData)
    {
        $this->order->setCustomerFirstname($expectedData['first_name']);
        $this->order->setCustomerSuffix($expectedData['customer_suffix']);
        $this->order->setCustomerPrefix($expectedData['customer_prefix']);
        $this->scopeConfigMock->expects($this->exactly($expectedData['invocation']))
            ->method('isSetFlag')
            ->willReturn(true);
        $this->assertEquals($expectedData['expected_name'], $this->order->getCustomerName());
    }

    /**
     * Customer name data provider
     */
    public function customerNameProvider()
    {
        return
            [
                [
                    [
                        'first_name' => null,
                        'invocation' => 0,
                        'expected_name' => 'Guest',
                        'customer_suffix' => 'smith',
                        'customer_prefix' => 'mr.'
                    ]
                ],
                [
                    [
                        'first_name' => 'Smith',
                        'invocation' => 1,
                        'expected_name' => 'mr. Smith  Carl',
                        'customer_suffix' => 'Carl',
                        'customer_prefix' => 'mr.'
                    ]
                ]
            ];
    }

    /**
     * @param string $status
     *
     * @dataProvider notInvoicingStatesProvider
     */
    public function testCanNotInvoiceInSomeStates($status)
    {
        $this->item->expects($this->any())
            ->method('getQtyToInvoice')
            ->willReturn(42);
        $this->item->expects($this->any())
            ->method('getLockedDoInvoice')
            ->willReturn(false);
        $this->order->setData('state', $status);
        $this->assertFalse($this->order->canInvoice());
    }

    public function testCanNotInvoiceWhenActionInvoiceFlagIsFalse()
    {
        $this->item->expects($this->any())
            ->method('getQtyToInvoice')
            ->willReturn(42);
        $this->item->expects($this->any())
            ->method('getLockedDoInvoice')
            ->willReturn(false);
        $this->order->setActionFlag(\Magento\Sales\Model\Order::ACTION_FLAG_INVOICE, false);
        $this->assertFalse($this->order->canInvoice());
    }

    /**
     * Test CanNotInvoice method when invoice is locked.
     *
     * @return void
     */
    public function testCanNotInvoiceWhenLockedInvoice()
    {
        $this->prepareOrderItem();

        $this->item->expects($this->any())
            ->method('getQtyToInvoice')
            ->willReturn(42);
        $this->item->expects($this->any())
            ->method('getLockedDoInvoice')
            ->willReturn(true);
        $this->assertFalse($this->order->canInvoice());
    }

    /**
     * Test CanNotInvoice method when didn't have qty to invoice.
     *
     * @return void
     */
    public function testCanNotInvoiceWhenDidNotHaveQtyToInvoice()
    {
        $this->prepareOrderItem();

        $this->item->expects($this->any())
            ->method('getQtyToInvoice')
            ->willReturn(0);
        $this->item->expects($this->any())
            ->method('getLockedDoInvoice')
            ->willReturn(false);
        $this->assertFalse($this->order->canInvoice());
    }

    public function testCanCreditMemo()
    {
        $totalPaid = 10;
        $this->prepareOrderItem();
        $this->order->setTotalPaid($totalPaid);
        $this->priceCurrency->expects($this->once())->method('round')->with($totalPaid)->willReturnArgument(0);
        $this->assertTrue($this->order->canCreditmemo());
    }

    public function testCanNotCreditMemoWithTotalNull()
    {
        $totalPaid = 0;
        $this->prepareOrderItem();
        $this->order->setTotalPaid($totalPaid);
        $this->priceCurrency->expects($this->once())->method('round')->with($totalPaid)->willReturnArgument(0);
        $this->assertFalse($this->order->canCreditmemo());
    }

    public function testCanNotCreditMemoWithAdjustmentNegative()
    {
        $totalPaid = 100;
        $adjustmentNegative = 10;
        $totalRefunded = 90;

        $this->prepareOrderItem();
        $this->order->setTotalPaid($totalPaid);
        $this->order->setTotalRefunded($totalRefunded);
        $this->order->setAdjustmentNegative($adjustmentNegative);
        $this->priceCurrency->expects($this->once())->method('round')->with($totalPaid)->willReturnArgument(0);

        $this->assertFalse($this->order->canCreditmemo());
    }

    public function testCanCreditMemoWithAdjustmentNegativeLowerThanTotalPaid()
    {
        $totalPaid = 100;
        $adjustmentNegative = 9;
        $totalRefunded = 90;

        $this->prepareOrderItem();
        $this->order->setTotalPaid($totalPaid);
        $this->order->setTotalRefunded($totalRefunded);
        $this->order->setAdjustmentNegative($adjustmentNegative);
        $this->priceCurrency->expects($this->once())->method('round')->with($totalPaid)->willReturnArgument(0);

        $this->assertTrue($this->order->canCreditmemo());
    }

    /**
     * @param string $state
     *
     * @dataProvider canNotCreditMemoStatesProvider
     */
    public function testCanNotCreditMemoWithSomeStates($state)
    {
        $this->order->setData('state', $state);
        $this->assertFalse($this->order->canCreditmemo());
    }

    public function testCanNotCreditMemoWithForced()
    {
        $this->order->setData('forced_can_creditmemo', true);
        $this->assertTrue($this->order->canCreditmemo());
    }

    public function testCanEditIfHasInvoices()
    {
        $invoiceCollection = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class)
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

    /**
     * @covers \Magento\Sales\Model\Order::canReorder
     */
    public function testCanReorder()
    {
        $productId = 1;

        $this->order->setState(Order::STATE_PROCESSING);
        $this->order->setActionFlag(Order::ACTION_FLAG_REORDER, true);

        $this->item->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);

        $product = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['isSalable'])
            ->getMockForAbstractClass();
        $product->expects(static::once())
            ->method('isSalable')
            ->willReturn(true);

        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'addIdFilter', 'load', 'getItemById', 'addAttributeToSelect'])
            ->getMock();
        $productCollection->expects($this->once())
            ->method('setStoreId')
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('addIdFilter')
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('getItemById')
            ->with($productId)
            ->willReturn($product);
        $this->productCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($productCollection);

        $this->assertTrue($this->order->canReorder());
    }

    /**
     * @covers \Magento\Sales\Model\Order::canReorder
     */
    public function testCanReorderIsPaymentReview()
    {
        $this->order->setState(Order::STATE_PAYMENT_REVIEW);

        $this->assertFalse($this->order->canReorder());
    }

    /**
     * @covers \Magento\Sales\Model\Order::canReorder
     */
    public function testCanReorderFlagReorderFalse()
    {
        $this->order->setState(Order::STATE_PROCESSING);
        $this->order->setActionFlag(Order::ACTION_FLAG_REORDER, false);

        $this->assertFalse($this->order->canReorder());
    }

    /**
     * @covers \Magento\Sales\Model\Order::canReorder
     */
    public function testCanReorderProductNotExists()
    {
        $productId = 1;

        $this->order->setState(Order::STATE_PROCESSING);
        $this->order->setActionFlag(Order::ACTION_FLAG_REORDER, true);

        $this->item->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);

        $product = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['isSalable'])
            ->getMockForAbstractClass();
        $product->expects(static::never())
            ->method('isSalable');

        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'addIdFilter', 'load', 'getItemById', 'addAttributeToSelect'])
            ->getMock();
        $productCollection->expects($this->once())
            ->method('setStoreId')
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('addIdFilter')
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('getItemById')
            ->with($productId)
            ->willReturn(null);
        $this->productCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($productCollection);

        $productCollection->expects($this->once())
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $this->assertFalse($this->order->canReorder());
    }

    /**
     * @covers \Magento\Sales\Model\Order::canReorder
     */
    public function testCanReorderProductNotSalable()
    {
        $productId = 1;

        $this->order->setState(Order::STATE_PROCESSING);
        $this->order->setActionFlag(Order::ACTION_FLAG_REORDER, true);

        $this->item->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);

        $product = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['isSalable'])
            ->getMockForAbstractClass();
        $product->expects(static::once())
            ->method('isSalable')
            ->willReturn(false);

        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'addIdFilter', 'load', 'getItemById', 'addAttributeToSelect'])
            ->getMock();
        $productCollection->expects($this->once())
            ->method('setStoreId')
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('addIdFilter')
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('getItemById')
            ->with($productId)
            ->willReturn($product);
        $this->productCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($productCollection);

        $productCollection->expects($this->once())
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $this->assertFalse($this->order->canReorder());
    }

    public function testCanCancelCanReviewPayment()
    {
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Payment::class)
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

    /**
     * Test CanCancelAllInvoiced method.
     *
     * @return void
     */
    public function testCanCancelAllInvoiced()
    {
        $this->prepareOrderItem();

        $paymentMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['isDeleted', 'canReviewPayment', 'canFetchTransactionInfo', '__wakeUp'])
            ->getMock();
        $paymentMock->expects($this->any())
            ->method('canReviewPayment')
            ->will($this->returnValue(false));
        $paymentMock->expects($this->any())
            ->method('canFetchTransactionInfo')
            ->will($this->returnValue(false));
        $collectionMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Item\Collection::class,
            ['getItems', 'setOrderFilter']
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
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Payment::class)
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
     * Test CanCancelActionFlag method.
     *
     * @param bool $cancelActionFlag
     * @dataProvider dataProviderActionFlag
     * @return void
     */
    public function testCanCancelActionFlag($cancelActionFlag)
    {
        $this->prepareOrderItem();

        $paymentMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Payment::class)
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
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var Order $order */
        $order = $helper->getObject(\Magento\Sales\Model\Order::class);
        foreach ($actionFlags as $action => $flag) {
            $order->setActionFlag($action, $flag);
        }
        $order->setData('state', $orderState);
        $payment = $this->_prepareOrderPayment($order);
        $canVoidOrder = true;

        if ($orderState == \Magento\Sales\Model\Order::STATE_CANCELED) {
            $canVoidOrder = false;
        }

        if ($orderState == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW) {
            $canVoidOrder = false;
        }
        if ($orderState == \Magento\Sales\Model\Order::STATE_HOLDED &&
            (!isset($actionFlags[\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD]) ||
                $actionFlags[\Magento\Sales\Model\Order::ACTION_FLAG_UNHOLD] !== false
            )
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

        $collectionMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Payment\Collection::class)
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
        $payment = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Payment::class
        )->disableOriginalConstructor()->getMock();
        foreach ($mockedMethods as $method => $value) {
            $payment->expects($this->any())->method($method)->will($this->returnValue($value));
        }
        $payment->expects($this->any())->method('isDeleted')->will($this->returnValue(false));

        $order->setData(\Magento\Sales\Api\Data\OrderInterface::PAYMENT, $payment);

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
        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['isDeleted', 'filterByTypes', 'filterByParent', 'getQtyToInvoice', '__wakeUp'])
            ->getMock();

        $itemMock->expects($this->any())
            ->method('getQtyToInvoice')
            ->will($this->returnValue($qtyInvoiced));

        $iterator = new \ArrayIterator([$itemMock]);

        $itemCollectionMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Item\Collection::class)
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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
            \Magento\Sales\Api\Data\OrderStatusHistoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setOrder']
        );
        $dbMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->setMethods(['setOrder'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $collectionMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\History\Collection::class,
            [
                'setOrderFilter',
                'setOrder',
                'getItems',
                'getIterator',
                'toOptionArray',
                'count',
                'load'
            ]
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

    public function testLoadByIncrementIdAndStoreId()
    {
        $incrementId = '000000001';
        $storeId = '2';
        $this->salesOrderCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->salesOrderCollectionMock);
        $this->salesOrderCollectionMock->expects($this->any())->method('addFieldToFilter')->willReturnSelf();
        $this->salesOrderCollectionMock->expects($this->once())->method('load')->willReturnSelf();
        $this->salesOrderCollectionMock->expects($this->once())->method('getFirstItem')->willReturn($this->order);
        $this->assertSame($this->order, $this->order->loadByIncrementIdAndStoreId($incrementId, $storeId));
    }

    public function testSetPaymentWithId()
    {
        $this->order->setId(123);
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->order->setData(OrderInterface::PAYMENT, $payment);
        $this->order->setDataChanges(false);

        $payment->expects($this->once())
            ->method('setOrder')
            ->with($this->order)
            ->willReturnSelf();

        $payment->expects($this->once())
            ->method('setParentId')
            ->with(123)
            ->willReturnSelf();

        $payment->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->order->setPayment($payment);

        $this->assertEquals(
            $this->order->getData(
                OrderInterface::PAYMENT
            ),
            $payment
        );

        $this->assertFalse(
            $this->order->hasDataChanges()
        );
    }

    public function testSetPaymentNoId()
    {
        $this->order->setId(123);
        $this->order->setDataChanges(false);

        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payment->expects($this->once())
            ->method('setOrder')
            ->with($this->order)
            ->willReturnSelf();

        $payment->expects($this->once())
            ->method('setParentId')
            ->with(123)
            ->willReturnSelf();

        $payment->expects($this->any())
            ->method('getId')
            ->willReturn(null);

        $this->order->setPayment($payment);

        $this->assertEquals(
            $this->order->getData(
                OrderInterface::PAYMENT
            ),
            $payment
        );

        $this->assertTrue(
            $this->order->hasDataChanges()
        );
    }

    public function testSetPaymentNull()
    {
        $this->assertEquals(null, $this->order->setPayment(null));

        $this->assertEquals(
            $this->order->getData(
                OrderInterface::PAYMENT
            ),
            null
        );

        $this->assertTrue(
            $this->order->hasDataChanges()
        );
    }

    public function testResetOrderWillResetPayment()
    {
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->order->setData(OrderInterface::PAYMENT, $payment);
        $this->order->reset();
        $this->assertEquals(
            $this->order->getData(
                OrderInterface::PAYMENT
            ),
            null
        );

        $this->assertTrue(
            $this->order->hasDataChanges()
        );
    }

    public function testGetCreatedAtFormattedUsesCorrectLocale()
    {
        $localeCode = 'nl_NL';

        $this->localeResolver->expects($this->once())->method('getDefaultLocale')->willReturn($localeCode);
        $this->timezone->expects($this->once())->method('formatDateTime')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $localeCode
            );

        $this->order->getCreatedAtFormatted(\IntlDateFormatter::SHORT);
    }

    /**
     * @return array
     */
    public function notInvoicingStatesProvider()
    {
        return [
            [\Magento\Sales\Model\Order::STATE_COMPLETE],
            [\Magento\Sales\Model\Order::STATE_CANCELED],
            [\Magento\Sales\Model\Order::STATE_CLOSED]
        ];
    }

    /**
     * @return array
     */
    public function canNotCreditMemoStatesProvider()
    {
        return [
            [\Magento\Sales\Model\Order::STATE_HOLDED],
            [\Magento\Sales\Model\Order::STATE_CANCELED],
            [\Magento\Sales\Model\Order::STATE_CLOSED],
            [\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW]
        ];
    }
}
