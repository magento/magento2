<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Model\Checkout\Type;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressSearchResultsInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Model\AllowedCountries;
use Magento\Directory\Model\Currency;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Multishipping\Helper\Data;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderDefault;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderFactory;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\SpecificationInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\ToOrder;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\Shipping;
use Magento\Quote\Model\ShippingAssignment;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class Multishipping
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class MultishippingTest extends TestCase
{
    /**
     * @var Multishipping
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $customerMock;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var MockObject
     */
    protected $addressRepositoryMock;

    /**
     * @var MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var MockObject
     */
    protected $totalsCollectorMock;

    /**
     * @var MockObject
     */
    private $cartExtensionFactoryMock;

    /**
     * @var MockObject
     */
    private $shippingAssignmentProcessorMock;

    /**
     * @var MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var OrderFactory|MockObject
     */
    private $orderFactoryMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var ToOrder|MockObject
     */
    private $toOrderMock;

    /**
     * @var ToOrderAddress|MockObject
     */
    private $toOrderAddressMock;

    /**
     * @var ToOrderPayment|MockObject
     */
    private $toOrderPaymentMock;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceMock;

    /**
     * @var ToOrderItem|MockObject
     */
    private $toOrderItemMock;

    /**
     * @var PlaceOrderFactory|MockObject
     */
    private $placeOrderFactoryMock;

    /**
     * @var Generic|MockObject
     */
    private $sessionMock;

    /**
     * @var MockObject
     */
    private $scopeConfigMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->checkoutSessionMock = $this->createSimpleMock(Session::class);
        $this->customerSessionMock = $this->createSimpleMock(CustomerSession::class);
        $this->orderFactoryMock = $this->createSimpleMock(OrderFactory::class);
        $eventManagerMock = $this->createSimpleMock(ManagerInterface::class);
        $this->scopeConfigMock = $this->createSimpleMock(ScopeConfigInterface::class);
        $this->sessionMock = $this->createSimpleMock(Generic::class);
        $addressFactoryMock = $this->createSimpleMock(AddressFactory::class);
        $this->toOrderMock = $this->createSimpleMock(ToOrder::class);
        $this->toOrderAddressMock = $this->createSimpleMock(ToOrderAddress::class);
        $this->toOrderPaymentMock = $this->createSimpleMock(ToOrderPayment::class);
        $this->toOrderItemMock = $this->createSimpleMock(ToOrderItem::class);
        $storeManagerMock = $this->createSimpleMock(StoreManagerInterface::class);
        $paymentSpecMock = $this->createSimpleMock(SpecificationInterface::class);
        $this->helperMock = $this->createSimpleMock(Data::class);
        $orderSenderMock = $this->createSimpleMock(OrderSender::class);
        $this->priceMock = $this->createSimpleMock(PriceCurrencyInterface::class);
        $this->quoteRepositoryMock = $this->createSimpleMock(CartRepositoryInterface::class);
        $this->filterBuilderMock = $this->createSimpleMock(FilterBuilder::class);
        $this->searchCriteriaBuilderMock = $this->createSimpleMock(SearchCriteriaBuilder::class);
        $this->addressRepositoryMock = $this->createSimpleMock(AddressRepositoryInterface::class);
        /** This is used to get past _init() which is called in construct. */
        $data['checkout_session'] = $this->checkoutSessionMock;
        $this->quoteMock = $this->createSimpleMock(Quote::class);
        $this->customerMock = $this->createSimpleMock(CustomerInterface::class);
        $this->customerMock->expects($this->atLeastOnce())->method('getId')->willReturn(null);
        $this->checkoutSessionMock->expects($this->atLeastOnce())->method('getQuote')->willReturn($this->quoteMock);
        $this->customerSessionMock->expects($this->atLeastOnce())->method('getCustomerDataObject')
            ->willReturn($this->customerMock);
        $this->totalsCollectorMock = $this->createSimpleMock(TotalsCollector::class);
        $this->cartExtensionFactoryMock = $this->getMockBuilder(CartExtensionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $allowedCountryReaderMock = $this->getMockBuilder(AllowedCountries::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllowedCountries'])
            ->getMock();
        $allowedCountryReaderMock->method('getAllowedCountries')
            ->willReturn(['EN'=>'EN']);
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['mergeDataObjects'])
            ->getMock();
        $this->placeOrderFactoryMock = $this->getMockBuilder(PlaceOrderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $logger = $this->createSimpleMock(LoggerInterface::class);

        $this->model = new Multishipping(
            $this->checkoutSessionMock,
            $this->customerSessionMock,
            $this->orderFactoryMock,
            $this->addressRepositoryMock,
            $eventManagerMock,
            $this->scopeConfigMock,
            $this->sessionMock,
            $addressFactoryMock,
            $this->toOrderMock,
            $this->toOrderAddressMock,
            $this->toOrderPaymentMock,
            $this->toOrderItemMock,
            $storeManagerMock,
            $paymentSpecMock,
            $this->helperMock,
            $orderSenderMock,
            $this->priceMock,
            $this->quoteRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->filterBuilderMock,
            $this->totalsCollectorMock,
            $data,
            $this->cartExtensionFactoryMock,
            $allowedCountryReaderMock,
            $this->placeOrderFactoryMock,
            $logger,
            $this->dataObjectHelperMock
        );

        $this->shippingAssignmentProcessorMock = $this->createSimpleMock(ShippingAssignmentProcessor::class);

        $objectManager = new ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'shippingAssignmentProcessor',
            $this->shippingAssignmentProcessorMock
        );
    }

    /**
     * Verify set shipping items information.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSetShippingItemsInformation(): void
    {
        $info = [
            [
                1 => [
                    'qty' => 2,
                    'address' => 42
                ]
            ]
        ];
        $this->quoteMock->expects($this->once())->method('getAllShippingAddresses')->willReturn([]);
        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);

        $this->helperMock->expects($this->once())->method('getMaximumQty')->willReturn(500);

        $this->quoteMock->expects($this->once())->method('getItemById')->with(array_keys($info[0])[0])
            ->willReturn(null);

        $this->quoteMock->expects($this->atLeastOnce())->method('getAllItems')->willReturn([]);
        $this->quoteMock->expects($this->once())
            ->method('__call')
            ->with('setTotalsCollectedFlag', [false])
            ->willReturnSelf();

        $this->filterBuilderMock->expects($this->atLeastOnce())->method('setField')->willReturnSelf();
        $this->filterBuilderMock->expects($this->atLeastOnce())->method('setValue')->willReturnSelf();
        $this->filterBuilderMock->expects($this->atLeastOnce())->method('setConditionType')->willReturnSelf();
        $this->filterBuilderMock->expects($this->atLeastOnce())->method('create')->willReturnSelf();

        $searchCriteriaMock = $this->createSimpleMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())->method('addFilters')->willReturnSelf();
        $this->searchCriteriaBuilderMock
            ->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $resultMock = $this->createSimpleMock(AddressSearchResultsInterface::class);
        $this->addressRepositoryMock->expects($this->atLeastOnce())->method('getList')->willReturn($resultMock);
        $addressItemMock = $this->createSimpleMock(AddressInterface::class);
        $resultMock->expects($this->atLeastOnce())->method('getItems')->willReturn([$addressItemMock]);
        $addressItemMock->expects($this->atLeastOnce())->method('getId')->willReturn(null);

        $this->mockShippingAssignment();

        $this->assertEquals($this->model, $this->model->setShippingItemsInformation($info));
    }

    /**
     * Verify set shipping items information for address leak
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSetShippingItemsInformationForAddressLeak(): void
    {
        $info = [
            [
                1 => [
                    'qty' => 2,
                    'address' => 43
                ]
            ]
        ];
        $customerAddressId = 42;

        $customerAddresses = [
            $this->getCustomerAddressMock($customerAddressId)
        ];

        $quoteItemMock = $this->createSimpleMock(Item::class);
        $this->quoteMock->expects($this->once())->method('getItemById')->willReturn($quoteItemMock);
        $this->quoteMock->expects($this->once())->method('getAllShippingAddresses')->willReturn([]);

        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);
        $this->helperMock->expects($this->once())->method('getMaximumQty')->willReturn(500);
        $this->customerMock->expects($this->once())->method('getAddresses')->willReturn($customerAddresses);

        $this->expectExceptionMessage('Verify the shipping address information and continue.');

        $this->assertEquals($this->model, $this->model->setShippingItemsInformation($info));
    }

    /**
     * Verify update quote customer shipping address.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testUpdateQuoteCustomerShippingAddress(): void
    {
        $addressId = 42;
        $customerAddressId = 42;

        $customerAddresses = [
            $this->getCustomerAddressMock($customerAddressId)
        ];

        $this->customerMock->expects($this->once())->method('getAddresses')->willReturn($customerAddresses);
        $this->addressRepositoryMock->expects($this->once())->method('getById')->willReturn(null);

        $this->assertEquals($this->model, $this->model->updateQuoteCustomerShippingAddress($addressId));
    }

    /**
     * Verify update quote customer shipping address for address leak
     *
     * @return void
     * @throws LocalizedException
     */
    public function testUpdateQuoteCustomerShippingAddressForAddressLeak(): void
    {
        $addressId = 43;
        $customerAddressId = 42;

        $customerAddresses = [
            $this->getCustomerAddressMock($customerAddressId)
        ];
        $this->customerMock->expects($this->once())->method('getAddresses')->willReturn($customerAddresses);
        $this->expectExceptionMessage('Verify the shipping address information and continue.');

        $this->assertEquals($this->model, $this->model->updateQuoteCustomerShippingAddress($addressId));
    }

    /**
     * Verify set quote customer billing address.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSetQuoteCustomerBillingAddress(): void
    {
        $addressId = 42;
        $customerAddressId = 42;

        $customerAddresses = [
            $this->getCustomerAddressMock($customerAddressId)
        ];
        $this->customerMock->expects($this->once())->method('getAddresses')->willReturn($customerAddresses);

        $this->assertEquals($this->model, $this->model->setQuoteCustomerBillingAddress($addressId));
    }

    /**
     * Verify set quote customer billing address for address leak.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSetQuoteCustomerBillingAddressForAddressLeak(): void
    {
        $addressId = 43;
        $customerAddressId = 42;

        $customerAddresses = [
            $this->getCustomerAddressMock($customerAddressId)
        ];
        $this->customerMock->expects($this->once())->method('getAddresses')->willReturn($customerAddresses);
        $this->expectExceptionMessage('Verify the billing address information and continue.');

        $this->assertEquals($this->model, $this->model->setQuoteCustomerBillingAddress($addressId));
    }

    /**
     * Verify get quote shipping addresses items.
     *
     * @return void
     */
    public function testGetQuoteShippingAddressesItems(): void
    {
        $quoteItem = $this->getMockBuilder(AddressItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getShippingAddressesItems')->willReturn($quoteItem);
        $this->model->getQuoteShippingAddressesItems();
    }

    /**
     * Verify set shipping methods
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSetShippingMethods(): void
    {
        $methodsArray = [1 => 'flatrate_flatrate', 2 => 'tablerate_bestway'];
        $addressId = 1;
        $addressMock = $this->getMockBuilder(QuoteAddress::class)
            ->setMethods(['getId', 'setShippingMethod', 'setCollectShippingRates'])
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock->expects($this->once())->method('getId')->willReturn($addressId);
        $this->quoteMock->expects($this->once())->method('getAllShippingAddresses')->willReturn([$addressMock]);
        $addressMock->expects($this->once())->method('setShippingMethod')->with($methodsArray[$addressId]);
        $addressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true);
        $this->quoteMock->expects($this->once())
            ->method('__call')
            ->with('setTotalsCollectedFlag', [false])
            ->willReturnSelf();

        $this->mockShippingAssignment();

        //save mock
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->model->setShippingMethods($methodsArray);
    }

    /**
     * Verify create orders
     *
     * @return void
     * @throws \Exception
     */
    public function testCreateOrders(): void
    {
        $addressTotal = 5;
        $productType = Type::TYPE_SIMPLE;
        $infoBuyRequest = [
            'info_buyRequest' => [
                'product' => '1',
                'qty' => 1,
            ],
        ];
        $quoteItemId = 1;
        $paymentProviderCode = 'checkmo';
        $shippingPrice = '0.00';
        $currencyCode = 'USD';
        $simpleProductTypeMock = $this->getMockBuilder(Simple::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrderOptions'])
            ->getMock();
        $productMock = $this->getProductMock($simpleProductTypeMock);
        $simpleProductTypeMock->method('getOrderOptions')->with($productMock)->willReturn($infoBuyRequest);

        $quoteItemMock = $this->getQuoteItemMock($productType, $productMock);
        $quoteAddressItemMock = $this->getQuoteAddressItemMock($quoteItemMock, $productType, $infoBuyRequest);
        list($shippingAddressMock, $billingAddressMock) =
            $this->getQuoteAddressesMock($quoteAddressItemMock, $addressTotal);
        $this->setQuoteMockData($paymentProviderCode, $shippingAddressMock, $billingAddressMock);

        $currencyMock = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->setMethods([ 'convert' ])
            ->getMock();
        $currencyMock->method('convert')->willReturn($shippingPrice);
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseCurrency','getCurrentCurrencyCode' ])
            ->getMock();
        $storeMock->method('getBaseCurrency')->willReturn($currencyMock);
        $storeMock->method('getCurrentCurrencyCode')->willReturn($currencyCode);
        $orderAddressMock = $this->createSimpleMock(OrderAddressInterface::class);
        $orderPaymentMock = $this->createSimpleMock(OrderPaymentInterface::class);
        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuoteItemId'])
            ->getMock();
        $orderItemMock->method('getQuoteItemId')->willReturn($quoteItemId);
        $orderMock = $this->getOrderMock($orderAddressMock, $orderPaymentMock, $orderItemMock);

        $orderMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $orderMock->expects($this->once())->method('setBaseShippingAmount')->with($shippingPrice)->willReturnSelf();
        $orderMock->expects($this->once())->method('setShippingAmount')->with($shippingPrice)->willReturnSelf();
        $this->orderFactoryMock->expects($this->once())->method('create')->willReturn($orderMock);
        $this->dataObjectHelperMock->expects($this->once())->method('mergeDataObjects')
            ->with(
                OrderInterface::class,
                $orderMock,
                $orderMock
            )->willReturnSelf();
        $this->priceMock->expects($this->once())->method('round')->with($addressTotal)->willReturn($addressTotal);

        $this->toOrderMock
            ->expects($this->once())
            ->method('convert')
            ->with($shippingAddressMock)
            ->willReturn($orderMock);
        $this->toOrderAddressMock->expects($this->exactly(2))->method('convert')
            ->withConsecutive(
                [$billingAddressMock, []],
                [$shippingAddressMock, []]
            )->willReturn($orderAddressMock);
        $this->toOrderPaymentMock->method('convert')->willReturn($orderPaymentMock);
        $this->toOrderItemMock->method('convert')->with($quoteAddressItemMock)->willReturn($orderItemMock);
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();

        $placeOrderServiceMock = $this->getMockBuilder(PlaceOrderDefault::class)
            ->disableOriginalConstructor()
            ->setMethods(['place'])
            ->getMock();
        $placeOrderServiceMock->method('place')->with([$orderMock])->willReturn([]);
        $this->placeOrderFactoryMock->method('create')->with($paymentProviderCode)->willReturn($placeOrderServiceMock);
        $this->quoteRepositoryMock->method('save')->with($this->quoteMock);

        $this->model->createOrders();
    }

    /**
     * Create orders verify exception message
     *
     * @param array $config
     *
     * @return void
     * @dataProvider getConfigCreateOrders
     * @throws \Exception
     */
    public function testCreateOrdersWithThrowsException(array $config): void
    {
        $simpleProductTypeMock = $this->getMockBuilder(Simple::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrderOptions'])
            ->getMock();
        $orderAddressMock = $this->createSimpleMock(OrderAddressInterface::class);
        $orderPaymentMock = $this->createSimpleMock(OrderPaymentInterface::class);
        $orderItemMock = $this->createMock(\Magento\Sales\Model\Order\Item::class);
        $productMock = $this->getProductMock($simpleProductTypeMock);
        $orderMock = $this->getOrderMock(
            $orderAddressMock,
            $orderPaymentMock,
            $orderItemMock
        );
        $quoteItemMock = $this->getQuoteItemMock($config['productType'], $productMock);
        $quoteAddressItemMock = $this->getQuoteAddressItemMock(
            $quoteItemMock,
            $config['productType'],
            $config['infoBuyRequest']
        );
        list($shippingAddressMock, $billingAddressMock) = $this->getQuoteAddressesMock(
            $quoteAddressItemMock,
            $config['addressTotal']
        );
        $this->setQuoteMockData(
            $config['paymentProviderCode'],
            $shippingAddressMock,
            $billingAddressMock
        );
        $currencyMock = $this->getCurrencyMock($config['shippingPrice']);
        $storeMock = $this->getStoreMock($currencyMock, $config['currencyCode']);
        $placeOrderServiceMock = $this->getMockBuilder(PlaceOrderDefault::class)
            ->disableOriginalConstructor()
            ->setMethods(['place'])
            ->getMock();

        $orderItemMock->method('getQuoteItemId')->willReturn($config['quoteItemId']);
        $simpleProductTypeMock->method('getOrderOptions')
            ->with($productMock)
            ->willReturn($config['infoBuyRequest']);
        $this->getOrderMockData($orderMock, $storeMock, $config['shippingPrice']);
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('mergeDataObjects')
            ->with(OrderInterface::class, $orderMock, $orderMock)
            ->willReturnSelf();
        $this->priceMock->expects($this->once())
            ->method('round')
            ->with($config['addressTotal'])
            ->willReturn($config['addressTotal']);
        $this->toOrderMock->expects($this->once())
            ->method('convert')
            ->with($shippingAddressMock)
            ->willReturn($orderMock);
        $this->toOrderAddressMock->expects($this->exactly(2))
            ->method('convert')
            ->withConsecutive([$billingAddressMock, []], [$shippingAddressMock, []])
            ->willReturn($orderAddressMock);
        $this->toOrderPaymentMock->method('convert')
            ->willReturn($orderPaymentMock);
        $this->toOrderItemMock->method('convert')
            ->with($quoteAddressItemMock)
            ->willReturn($orderItemMock);
        $placeOrderServiceMock->method('place')
            ->with([$orderMock])
            ->willReturn([$config['quoteId'] => new \Exception()]);
        $this->quoteMock->expects($this->any())
            ->method('__call')
            ->willReturnSelf();
        $this->placeOrderFactoryMock->method('create')
            ->with($config['paymentProviderCode'])
            ->willReturn($placeOrderServiceMock);
        $this->quoteMock->expects($this->exactly(2))
            ->method('collectTotals')
            ->willReturnSelf();
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock);

        $this->expectExceptionMessage('Quote address for failed order ID "1" not found.');

        $this->model->createOrders();
    }

    /**
     * Return Store Mock.
     *
     * @param MockObject $currencyMock
     * @param string $currencyCode
     * @return MockObject
     */
    private function getStoreMock($currencyMock, string $currencyCode): MockObject
    {
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseCurrency','getCurrentCurrencyCode' ])
            ->getMock();
        $storeMock->method('getBaseCurrency')
            ->willReturn($currencyMock);
        $storeMock->method('getCurrentCurrencyCode')
            ->willReturn($currencyCode);

        return $storeMock;
    }

    /**
     * Return Order Mock with data
     *
     * @param MockObject $orderMock
     * @param MockObject $storeMock
     * @param string $shippingPrice
     * @return void
     */
    private function getOrderMockData(
        MockObject $orderMock,
        MockObject $storeMock,
        string $shippingPrice
    ): void {
        $orderMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $orderMock->expects($this->once())
            ->method('setBaseShippingAmount')
            ->with($shippingPrice)
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('setShippingAmount')
            ->with($shippingPrice)
            ->willReturnSelf();
    }

    /**
     * Return Payment Mock.
     *
     * @param string $paymentProviderCode
     * @return MockObject
     */
    private function getPaymentMock(string $paymentProviderCode): MockObject
    {
        $abstractMethod = $this->getMockBuilder(AbstractMethod::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAvailable'])
            ->getMockForAbstractClass();
        $abstractMethod->method('isAvailable')->willReturn(true);

        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance', 'getMethod'])
            ->getMock();
        $paymentMock->method('getMethodInstance')->willReturn($abstractMethod);
        $paymentMock->method('getMethod')->willReturn($paymentProviderCode);

        return $paymentMock;
    }

    /**
     * Return Product Mock.
     *
     * @param Simple|MockObject $simpleProductTypeMock
     * @return MockObject
     */
    private function getProductMock($simpleProductTypeMock): MockObject
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeInstance'])
            ->getMock();
        $productMock->method('getTypeInstance')->willReturn($simpleProductTypeMock);

        return $productMock;
    }

    /**
     * Return Quote Item Mock.
     *
     * @param string $productType
     * @param \Magento\Catalog\Model\Product|MockObject $productMock
     * @return MockObject
     */
    private function getQuoteItemMock($productType, $productMock): MockObject
    {
        $quoteItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductType', 'getProduct'])
            ->getMock();
        $quoteItemMock->method('getProductType')->willReturn($productType);
        $quoteItemMock->method('getProduct')->willReturn($productMock);

        return $quoteItemMock;
    }

    /**
     * Return Quote Address Item Mock
     *
     * @param \Magento\Quote\Model\Quote\Item|MockObject $quoteItemMock
     * @param string $productType
     * @param array $infoBuyRequest
     * @return MockObject
     */
    private function getQuoteAddressItemMock($quoteItemMock, string $productType, array $infoBuyRequest): MockObject
    {
        $quoteAddressItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuoteItem', 'setProductType', 'setProductOptions', 'getParentItem'])
            ->getMock();
        $quoteAddressItemMock->method('getQuoteItem')->willReturn($quoteItemMock);
        $quoteAddressItemMock->method('setProductType')->with($productType)->willReturnSelf();
        $quoteAddressItemMock->method('setProductOptions')->willReturn($infoBuyRequest);
        $quoteAddressItemMock->method('getParentItem')->willReturn(false);

        return $quoteAddressItemMock;
    }

    /**
     * Return Quote Addresses Mock
     * @param \Magento\Quote\Model\Quote\Address\Item|MockObject $quoteAddressItemMock
     * @param int $addressTotal
     * @return array
     */
    private function getQuoteAddressesMock($quoteAddressItemMock, int $addressTotal): array
    {
        $shippingAddressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'validate',
                    'getShippingMethod',
                    'getShippingRateByCode',
                    'getCountryId',
                    'getAddressType',
                    'getGrandTotal',
                    'getAllItems',
                ]
            )->getMock();
        $shippingAddressMock->method('validate')->willReturn(true);
        $shippingAddressMock->method('getShippingMethod')->willReturn('carrier');
        $shippingAddressMock->method('getCountryId')->willReturn('EN');
        $shippingAddressMock->method('getAllItems')->willReturn([$quoteAddressItemMock]);
        $shippingAddressMock->method('getAddressType')->willReturn('shipping');
        $shippingAddressMock->method('getGrandTotal')->willReturn($addressTotal);

        $shippingRateMock = $this->getMockBuilder(Rate::class)
            ->disableOriginalConstructor()
            ->setMethods([ 'getPrice' ])
            ->getMock();
        $shippingRateMock->method('getPrice')->willReturn('0.00');
        $shippingAddressMock->method('getShippingRateByCode')->willReturn($shippingRateMock);

        $billingAddressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();
        $billingAddressMock->method('validate')->willReturn(true);

        return [$shippingAddressMock, $billingAddressMock];
    }

    /**
     * Set data for Quote Mock.
     *
     * @param string $paymentProviderCode
     * @param Address|MockObject $shippingAddressMock
     * @param Address|MockObject $billingAddressMock
     *
     * @return void
     */
    private function setQuoteMockData(string $paymentProviderCode, $shippingAddressMock, $billingAddressMock): void
    {
        $quoteId = 1;
        $paymentMock = $this->getPaymentMock($paymentProviderCode);
        $this->quoteMock->method('getPayment')
            ->willReturn($paymentMock);
        $this->quoteMock->method('getAllShippingAddresses')
            ->willReturn([$shippingAddressMock]);
        $this->quoteMock->method('getBillingAddress')
            ->willReturn($billingAddressMock);
        $this->quoteMock->method('hasVirtualItems')
            ->willReturn(false);
        $this->quoteMock->expects($this->once())->method('reserveOrderId')->willReturnSelf();
        $this->quoteMock->method('getId')->willReturn($quoteId);
        $this->quoteMock->method('setIsActive')->with(false)->willReturnSelf();
    }

    /**
     * Return Order Mock.
     *
     * @param OrderAddressInterface|MockObject $orderAddressMock
     * @param OrderPaymentInterface|MockObject $orderPaymentMock
     * @param \Magento\Sales\Model\Order\Item|MockObject $orderItemMock
     * @return MockObject
     */
    private function getOrderMock($orderAddressMock, $orderPaymentMock, $orderItemMock): MockObject
    {
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setQuote',
                    'setBillingAddress',
                    'setShippingAddress',
                    'setPayment',
                    'addItem',
                    'getIncrementId',
                    'getId',
                    'getEntityId',
                    'getCanSendNewEmailFlag',
                    'getItems',
                    'setShippingMethod',
                    'getStore',
                    'setShippingAmount',
                    'setBaseShippingAmount'
                ]
            )->getMock();
        $orderMock->method('setQuote')->with($this->quoteMock);
        $orderMock->method('setBillingAddress')->with($orderAddressMock)->willReturnSelf();
        $orderMock->method('setShippingAddress')->with($orderAddressMock)->willReturnSelf();
        $orderMock->expects($this->once())->method('setShippingMethod')->with('carrier')->willReturnSelf();
        $orderMock->method('setPayment')->with($orderPaymentMock)->willReturnSelf();
        $orderMock->method('addItem')->with($orderItemMock)->willReturnSelf();
        $orderMock->method('getIncrementId')->willReturn('1');
        $orderMock->method('getId')->willReturn('1');
        $orderMock->method('getEntityId')->willReturn('1');
        $orderMock->method('getCanSendNewEmailFlag')->willReturn(false);
        $orderMock->method('getItems')->willReturn([$orderItemMock]);

        return $orderMock;
    }

    /**
     * Tests exception for addresses with country id not in the allowed countries list.
     *
     * @return void
     * @throws \Exception
     */
    public function testCreateOrdersCountryNotPresentInAllowedListException(): void
    {
        $exceptionMessage = 'Some addresses can\'t be used due to the configurations for specific countries.';

        $abstractMethod = $this->getMockBuilder(AbstractMethod::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAvailable'])
            ->getMockForAbstractClass();
        $abstractMethod->method('isAvailable')
            ->willReturn(true);

        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance'])
            ->getMock();
        $paymentMock->method('getMethodInstance')
            ->willReturn($abstractMethod);

        $shippingAddressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate', 'getShippingMethod', 'getShippingRateByCode', 'getCountryId'])
            ->getMock();
        $shippingAddressMock->method('validate')
            ->willReturn(true);
        $shippingAddressMock->method('getShippingMethod')
            ->willReturn('carrier');
        $shippingAddressMock->method('getShippingRateByCode')
            ->willReturn('code');
        $shippingAddressMock->method('getCountryId')
            ->willReturn('EU');

        $this->quoteMock->method('getPayment')
            ->willReturn($paymentMock);
        $this->quoteMock->method('getAllShippingAddresses')
            ->willReturn([$shippingAddressMock]);
        $this->expectExceptionMessage($exceptionMessage);

        $this->model->createOrders();
    }

    /**
     * Verify validate minimum amount multi address is false.
     *
     * @return void
     */
    public function testValidateMinimumAmountMultiAddressFalse(): void
    {
        $addressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);

        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('isSetFlag')
            ->withConsecutive(
                ['sales/minimum_order/active', ScopeInterface::SCOPE_STORE],
                ['sales/minimum_order/multi_address', ScopeInterface::SCOPE_STORE]
            )->willReturnOnConsecutiveCalls(true, false);

        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                ['sales/minimum_order/amount', ScopeInterface::SCOPE_STORE],
                ['sales/minimum_order/tax_including', ScopeInterface::SCOPE_STORE]
            )->willReturnOnConsecutiveCalls(100, false);

        $this->checkoutSessionMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);
        $this->quoteMock->expects($this->once())
            ->method('getAllAddresses')
            ->willReturn([$addressMock]);
        $addressMock->expects($this->once())
            ->method('getBaseSubtotalWithDiscount')
            ->willReturn(101);

        $this->assertTrue($this->model->validateMinimumAmount());
    }

    /**
     * Verify validate minimum amount multi address is true.
     *
     * @return void
     */
    public function testValidateMinimumAmountMultiAddressTrue(): void
    {
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('isSetFlag')
            ->withConsecutive(
                ['sales/minimum_order/active', ScopeInterface::SCOPE_STORE],
                ['sales/minimum_order/multi_address', ScopeInterface::SCOPE_STORE]
            )->willReturnOnConsecutiveCalls(true, true);

        $this->checkoutSessionMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('validateMinimumAmount')
            ->willReturn(false);

        $this->assertFalse($this->model->validateMinimumAmount());
    }

    /**
     * Return Extension Attributes Mock.
     *
     * @param ShippingAssignment $shippingAssignmentMock
     * @return CartExtension|MockObject
     */
    private function getExtensionAttributesMock(ShippingAssignment $shippingAssignmentMock): MockObject
    {
        $extensionAttributesMock = $this->getMockBuilder(CartExtension::class)
            ->setMethods(['setShippingAssignments'])
            ->getMock();

        $extensionAttributesMock
            ->expects($this->once())
            ->method('setShippingAssignments')
            ->with([$shippingAssignmentMock])
            ->willReturnSelf();

        return $extensionAttributesMock;
    }

    /**
     * Return Shipping Assignment Mock.
     *
     * @return ShippingAssignment | MockObject
     */
    private function getShippingAssignmentMock(): MockObject
    {
        $shippingAssignmentMock = $this->getMockBuilder(ShippingAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShipping', 'setShipping'])
            ->getMock();
        $shippingMock = $this->getMockBuilder(Shipping::class)
            ->disableOriginalConstructor()
            ->setMethods(['setMethod'])
            ->getMock();

        $shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn($shippingMock);
        $shippingMock->expects($this->once())->method('setMethod')->with(null)->willReturnSelf();
        $shippingAssignmentMock->expects($this->once())->method('setShipping')->with($shippingMock);

        return $shippingAssignmentMock;
    }

    /**
     * Expected shipping assignment
     *
     * @return void
     */
    private function mockShippingAssignment(): void
    {
        $shippingAssignmentMock = $this->getShippingAssignmentMock();

        $extensionAttributesMock = $this->getExtensionAttributesMock($shippingAssignmentMock);

        $this->shippingAssignmentProcessorMock
            ->expects($this->once())
            ->method('create')
            ->with($this->quoteMock)
            ->willReturn($shippingAssignmentMock);

        $this->quoteMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesMock);

        $this->quoteMock
            ->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributesMock);
    }

    /**
     * Return Customer Address Mock
     *
     * @param $customerAddressId
     * @return Address | MockObject
     */
    private function getCustomerAddressMock($customerAddressId): MockObject
    {
        $customerAddressMock = $this->getMockBuilder(Address::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerAddressMock
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerAddressId);
        return $customerAddressMock;
    }

    /**
     * Return Simple Mock.
     *
     * @param string $className
     * @return MockObject
     */
    private function createSimpleMock($className): MockObject
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Return Currency Mock.
     *
     * @param $shippingPrice
     * @return MockObject
     */
    private function getCurrencyMock($shippingPrice): MockObject
    {
        $currencyMock = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->setMethods([ 'convert' ])
            ->getMock();
        $currencyMock->method('convert')->willReturn($shippingPrice);
        return $currencyMock;
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getConfigCreateOrders(): array
    {
        return [
            [
                [
                    'quoteId'       => 1,
                    'addressTotal'  => 5,
                    'productType'  =>  Type::TYPE_SIMPLE,
                    'infoBuyRequest'=> [
                        'info_buyRequest' => [
                            'product' => '1',
                            'qty' => 1,
                        ],
                    ],
                    'quoteItemId'  => 1,
                    'paymentProviderCode' => 'checkmo',
                    'shippingPrice' => '0.00',
                    'currencyCode' => 'USD',
                ]
            ]
        ];
    }
}
