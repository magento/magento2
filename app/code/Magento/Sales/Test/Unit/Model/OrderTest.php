<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\Manager;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as OrderInvoiceCollection;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as OrderItemCollection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Payment\Collection as PaymentCollection;
use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory as PaymentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\History\Collection as HistoryCollection;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory as HistoryCollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Sales\Model\Order
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class OrderTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentCollectionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderItemCollectionFactoryMock;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManager;

    /**
     * @var string
     */
    protected $incrementId;

    /**
     * @var Item|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $item;

    /**
     * @var HistoryCollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $historyCollectionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var OrderCollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $salesOrderCollectionFactoryMock;

    /**
     * @var OrderCollection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $salesOrderCollectionMock;

    /**
     * @var ProductCollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productCollectionFactoryMock;

    /**
     * @var ResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $localeResolver;

    /**
     * @var TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $timezone;

    /**
     * @var OrderItemRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $itemRepository;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var MockObject|ScopeConfigInterface $scopeConfigMock
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->paymentCollectionFactoryMock = $this->createPartialMock(
            PaymentCollectionFactory::class,
            ['create']
        );
        $this->orderItemCollectionFactoryMock = $this->createPartialMock(
            OrderItemCollectionFactory::class,
            ['create']
        );
        $this->historyCollectionFactoryMock = $this->createPartialMock(
            HistoryCollectionFactory::class,
            ['create']
        );
        $this->productCollectionFactoryMock = $this->createPartialMock(
            ProductCollectionFactory::class,
            ['create']
        );
        $this->salesOrderCollectionFactoryMock = $this->createPartialMock(
            OrderCollectionFactory::class,
            ['create']
        );
        $this->item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->salesOrderCollectionMock = $this->getMockBuilder(
            OrderCollection::class
        )->disableOriginalConstructor()
            ->onlyMethods(['addFieldToFilter', 'load', 'getFirstItem'])
            ->getMock();
        $collection = $this->createMock(OrderItemCollection::class);
        $collection->expects($this->any())->method('setOrderFilter')->willReturnSelf();
        $collection->expects($this->any())->method('getItems')->willReturn([$this->item]);
        $collection->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator([$this->item]));
        $this->orderItemCollectionFactoryMock->expects($this->any())->method('create')->willReturn($collection);

        $this->priceCurrency = $this->getMockForAbstractClass(
            PriceCurrencyInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['round']
        );
        $this->localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->timezone = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->incrementId = '#00000001';
        $this->eventManager = $this->createMock(Manager::class);
        $context = $this->createPartialMock(Context::class, ['getEventDispatcher']);
        $context->expects($this->any())->method('getEventDispatcher')->willReturn($this->eventManager);

        $this->itemRepository = $this->getMockBuilder(OrderItemRepositoryInterface::class)
            ->onlyMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->onlyMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->order = $helper->getObject(
            Order::class,
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
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);

        $itemsCollection = $this->getMockBuilder(OrderItemSearchResultInterface::class)
            ->onlyMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
            OrderInterface::ITEMS,
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
        $this->assertNull($this->order->getItemById($fakeOrderItemId));
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
    public static function dataProviderGetItemByQuoteItemId()
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
    public static function dataProviderGetAllVisibleItems()
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
        $this->order->setActionFlag(Order::ACTION_FLAG_UNHOLD, true);
        $this->order->setState(Order::STATE_PAYMENT_REVIEW);
        $this->assertFalse($this->order->canCancel());
    }

    public function testCanCancelIsPaymentReview()
    {
        $this->order->setActionFlag(Order::ACTION_FLAG_UNHOLD, false);
        $this->order->setState(Order::STATE_PAYMENT_REVIEW);
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
        $this->order->setCustomerMiddlename($expectedData['middle_name']);
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
    public static function customerNameProvider()
    {
        return
            [
                [
                    [
                        'first_name' => null,
                        'invocation' => 0,
                        'middle_name' => null,
                        'expected_name' => 'Guest',
                        'customer_suffix' => 'smith',
                        'customer_prefix' => 'mr.'
                    ]
                ],
                [
                    [
                        'first_name' => 'Smith',
                        'invocation' => 0,
                        'middle_name' => null,
                        'expected_name' => 'mr. Smith  Carl',
                        'customer_suffix' => 'Carl',
                        'customer_prefix' => 'mr.'
                    ]
                ],
                [
                    [
                        'first_name' => 'John',
                        'invocation' => 1,
                        'middle_name' => 'Middle',
                        'expected_name' => 'mr. John Middle  Carl',
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
        $this->order->setActionFlag(Order::ACTION_FLAG_INVOICE, false);
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

    /**
     * Test canCreditMemo when the forced_can_creditmemo flag set to false.
     *
     * @return void
     */
    public function testCanNotCreditMemoWithForcedWhenFlagSetToFalse()
    {
        $this->prepareOrderItem();
        $this->order->setData('forced_can_creditmemo', false);
        $this->order->setState(Order::STATE_PROCESSING);
        $this->assertFalse($this->order->canCreditmemo());
    }

    public function testCanEditIfHasInvoices()
    {
        $invoiceCollection = $this->getMockBuilder(OrderInvoiceCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['count'])
            ->getMock();

        $invoiceCollection->expects($this->once())
            ->method('count')
            ->willReturn(2);

        $this->order->setInvoiceCollection($invoiceCollection);
        $this->order->setState(Order::STATE_PROCESSING);

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
            ->addMethods(['isSalable'])
            ->getMockForAbstractClass();
        $product->expects(static::once())
            ->method('isSalable')
            ->willReturn(true);

        $productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setStoreId', 'addIdFilter', 'load', 'getItemById', 'addAttributeToSelect'])
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
            ->addMethods(['isSalable'])
            ->getMockForAbstractClass();
        $product->expects(static::never())
            ->method('isSalable');

        $productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setStoreId', 'addIdFilter', 'load', 'getItemById', 'addAttributeToSelect'])
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
            ->addMethods(['isSalable'])
            ->getMockForAbstractClass();
        $product->expects(static::once())
            ->method('isSalable')
            ->willReturn(false);

        $productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setStoreId', 'addIdFilter', 'load', 'getItemById', 'addAttributeToSelect'])
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
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->addMethods(['isDeleted', 'canReviewPayment', 'canFetchTransactionInfo'])
            ->getMock();
        $paymentMock->expects($this->any())
            ->method('canReviewPayment')
            ->willReturn(false);
        $paymentMock->expects($this->any())
            ->method('canFetchTransactionInfo')
            ->willReturn(true);
        $this->preparePaymentMock($paymentMock);
        $this->order->setActionFlag(Order::ACTION_FLAG_UNHOLD, false);
        $this->order->setState(Order::STATE_PAYMENT_REVIEW);
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

        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->addMethods(['isDeleted', 'canReviewPayment', 'canFetchTransactionInfo'])
            ->getMock();
        $paymentMock->expects($this->any())
            ->method('canReviewPayment')
            ->willReturn(false);
        $paymentMock->expects($this->any())
            ->method('canFetchTransactionInfo')
            ->willReturn(false);
        $collectionMock = $this->createPartialMock(
            OrderItemCollection::class,
            ['getItems', 'setOrderFilter']
        );
        $this->orderItemCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->any())
            ->method('setOrderFilter')
            ->willReturnSelf();
        $this->preparePaymentMock($paymentMock);

        $this->prepareItemMock(0);

        $this->order->setActionFlag(Order::ACTION_FLAG_UNHOLD, false);
        $this->order->setState(Order::STATE_NEW);

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
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->addMethods(['isDeleted', 'canReviewPayment', 'canFetchTransactionInfo'])
            ->getMock();
        $paymentMock->expects($this->any())
            ->method('canReviewPayment')
            ->willReturn(false);
        $paymentMock->expects($this->any())
            ->method('canFetchTransactionInfo')
            ->willReturn(false);

        $this->preparePaymentMock($paymentMock);

        $this->prepareItemMock(1);
        $this->order->setActionFlag(Order::ACTION_FLAG_UNHOLD, false);
        $this->order->setState(Order::STATE_CANCELED);
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

        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->addMethods(['isDeleted', 'canReviewPayment', 'canFetchTransactionInfo'])
            ->getMock();
        $paymentMock->expects($this->any())
            ->method('canReviewPayment')
            ->willReturn(false);
        $paymentMock->expects($this->any())
            ->method('canFetchTransactionInfo')
            ->willReturn(false);

        $this->preparePaymentMock($paymentMock);

        $this->prepareItemMock(1);

        $actionFlags = [
            Order::ACTION_FLAG_UNHOLD => false,
            Order::ACTION_FLAG_CANCEL => $cancelActionFlag,
        ];
        foreach ($actionFlags as $action => $flag) {
            $this->order->setActionFlag($action, $flag);
        }
        $this->order->setData('state', Order::STATE_NEW);

        $this->item->expects($this->any())
            ->method('isDeleted')
            ->willReturn(false);
        $this->item->expects($this->any())
            ->method('getQtyToInvoice')
            ->willReturn(42);

        $this->assertEquals($cancelActionFlag, $this->order->canCancel());
    }

    public function testRegisterDiscountCanceled()
    {
        $this->item->expects($this->any())
            ->method('getQtyToInvoice')
            ->willReturn(42);
        $this->prepareOrderItem();
        $this->order->setDiscountAmount(-30);
        $this->order->setDiscountInvoiced(-10);
        $this->order->setBaseDiscountAmount(-30);
        $this->order->setBaseDiscountInvoiced(-10);
        $this->order->registerCancellation();
        $this->assertEquals(20, abs((float) $this->order->getDiscountCanceled()));
    }

    /**
     * @param array $actionFlags
     * @param string $orderState
     * @dataProvider canVoidPaymentDataProvider
     */
    public function testCanVoidPayment($actionFlags, $orderState)
    {
        $helper = new ObjectManager($this);
        /** @var Order $order */
        $order = $helper->getObject(Order::class);
        foreach ($actionFlags as $action => $flag) {
            $order->setActionFlag($action, $flag);
        }
        $order->setData('state', $orderState);
        $payment = $this->_prepareOrderPayment($order);
        $canVoidOrder = true;

        if ($orderState == Order::STATE_CANCELED) {
            $canVoidOrder = false;
        }

        if ($orderState == Order::STATE_PAYMENT_REVIEW) {
            $canVoidOrder = false;
        }
        if ($orderState == Order::STATE_HOLDED &&
            (
                !isset($actionFlags[Order::ACTION_FLAG_UNHOLD]) ||
                $actionFlags[Order::ACTION_FLAG_UNHOLD] !== false
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
            )->willReturn(
                $expected
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

        $collectionMock = $this->getMockBuilder(PaymentCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setOrderFilter', 'getIterator'])
            ->getMock();
        $collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn($iterator);
        $collectionMock->expects($this->any())
            ->method('setOrderFilter')->willReturnSelf();

        $this->paymentCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($collectionMock);
    }

    /**
     * Prepare payment for the order
     *
     * @param \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject $order
     * @param array $mockedMethods
     * @return \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function _prepareOrderPayment($order, $mockedMethods = [])
    {
        $payment = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Payment::class
        )->disableOriginalConstructor()
            ->getMock();
        foreach ($mockedMethods as $method => $value) {
            $payment->expects($this->any())->method($method)->willReturn($value);
        }
        $payment->expects($this->any())->method('isDeleted')->willReturn(false);

        $order->setData(OrderInterface::PAYMENT, $payment);

        return $payment;
    }

    /**
     * Get action flags
     *
     */
    protected static function _getActionFlagsValues()
    {
        return [
            [],
            [
                Order::ACTION_FLAG_UNHOLD => false,
                Order::ACTION_FLAG_CANCEL => false
            ],
            [
                Order::ACTION_FLAG_UNHOLD => false,
                Order::ACTION_FLAG_CANCEL => true
            ]
        ];
    }

    /**
     * Get order statuses
     *
     * @return array
     */
    protected static function _getOrderStatuses()
    {
        return [
            Order::STATE_HOLDED,
            Order::STATE_PAYMENT_REVIEW,
            Order::STATE_CANCELED,
            Order::STATE_COMPLETE,
            Order::STATE_CLOSED,
            Order::STATE_PROCESSING
        ];
    }

    /**
     * @param int $qtyInvoiced
     * @return void
     */
    protected function prepareItemMock($qtyInvoiced)
    {
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['filterByTypes', 'filterByParent'])
            ->onlyMethods(['isDeleted', 'getQtyToInvoice'])
            ->getMock();

        $itemMock->expects($this->any())
            ->method('getQtyToInvoice')
            ->willReturn($qtyInvoiced);

        $iterator = new \ArrayIterator([$itemMock]);

        $itemCollectionMock = $this->getMockBuilder(OrderItemCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setOrderFilter', 'getIterator', 'getItems'])
            ->getMock();
        $itemCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn($iterator);
        $itemCollectionMock->expects($this->any())
            ->method('setOrderFilter')->willReturnSelf();

        $this->orderItemCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemCollectionMock);
    }

    /**
     * @return array
     */
    public static function canVoidPaymentDataProvider()
    {
        $data = [];
        foreach (self::_getActionFlagsValues() as $actionFlags) {
            foreach (self::_getOrderStatuses() as $status) {
                $data[] = [$actionFlags, $status];
            }
        }
        return $data;
    }

    /**
     * @return array
     */
    public static function dataProviderActionFlag()
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
            OrderStatusHistoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setOrder']
        );
        $dbMock = $this->getMockBuilder(AbstractDb::class)
            ->onlyMethods(['setOrder'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $collectionMock = $this->createPartialMock(
            HistoryCollection::class,
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
        $this->assertNull($this->order->setPayment(null));

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
    public static function notInvoicingStatesProvider()
    {
        return [
            [Order::STATE_COMPLETE],
            [Order::STATE_CANCELED],
            [Order::STATE_CLOSED]
        ];
    }

    /**
     * @return array
     */
    public static function canNotCreditMemoStatesProvider()
    {
        return [
            [Order::STATE_HOLDED],
            [Order::STATE_CANCELED],
            [Order::STATE_CLOSED],
            [Order::STATE_PAYMENT_REVIEW]
        ];
    }
}
