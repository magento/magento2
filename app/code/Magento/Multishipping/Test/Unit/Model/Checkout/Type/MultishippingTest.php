<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Unit\Model\Checkout\Type;

use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressSearchResultsInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderDefault;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderFactory;
use Magento\Quote\Model\Quote\Address;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Multishipping\Helper\Data;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Payment\Model\Method\SpecificationInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Address\ToOrder;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\Shipping;
use Magento\Quote\Model\ShippingAssignment;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit_Framework_MockObject_MockObject;
use \PHPUnit\Framework\TestCase;
use Magento\Quote\Model\Quote\Payment;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Directory\Model\AllowedCountries;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class MultishippingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Multishipping
     */
    protected $model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepositoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsCollectorMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $cartExtensionFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentProcessorMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var OrderFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $orderFactoryMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var ToOrder|PHPUnit_Framework_MockObject_MockObject
     */
    private $toOrderMock;

    /**
     * @var ToOrderAddress|PHPUnit_Framework_MockObject_MockObject
     */
    private $toOrderAddressMock;

    /**
     * @var ToOrderPayment|PHPUnit_Framework_MockObject_MockObject
     */
    private $toOrderPaymentMock;

    /**
     * @var PriceCurrencyInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $priceMock;

    /**
     * @var ToOrderItem|PHPUnit_Framework_MockObject_MockObject
     */
    private $toOrderItemMock;

    /**
     * @var PlaceOrderFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $placeOrderFactoryMock;

    /**
     * @var Generic|PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    protected function setUp()
    {
        $this->checkoutSessionMock = $this->createSimpleMock(Session::class);
        $this->customerSessionMock = $this->createSimpleMock(CustomerSession::class);
        $this->orderFactoryMock = $this->createSimpleMock(OrderFactory::class);
        $eventManagerMock = $this->createSimpleMock(ManagerInterface::class);
        $scopeConfigMock = $this->createSimpleMock(ScopeConfigInterface::class);
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
        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['mergeDataObjects'])
            ->getMock();
        $this->placeOrderFactoryMock = $this->getMockBuilder(PlaceOrderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $logger = $this->createSimpleMock(\Psr\Log\LoggerInterface::class);

        $this->model = new Multishipping(
            $this->checkoutSessionMock,
            $this->customerSessionMock,
            $this->orderFactoryMock,
            $this->addressRepositoryMock,
            $eventManagerMock,
            $scopeConfigMock,
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

    public function testSetShippingItemsInformation()
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Verify the shipping address information and continue.
     */
    public function testSetShippingItemsInformationForAddressLeak()
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

        $this->assertEquals($this->model, $this->model->setShippingItemsInformation($info));
    }

    public function testUpdateQuoteCustomerShippingAddress()
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Verify the shipping address information and continue.
     */
    public function testUpdateQuoteCustomerShippingAddressForAddressLeak()
    {
        $addressId = 43;
        $customerAddressId = 42;

        $customerAddresses = [
            $this->getCustomerAddressMock($customerAddressId)
        ];
        $this->customerMock->expects($this->once())->method('getAddresses')->willReturn($customerAddresses);

        $this->assertEquals($this->model, $this->model->updateQuoteCustomerShippingAddress($addressId));
    }

    public function testSetQuoteCustomerBillingAddress()
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Verify the billing address information and continue.
     */
    public function testSetQuoteCustomerBillingAddressForAddressLeak()
    {
        $addressId = 43;
        $customerAddressId = 42;

        $customerAddresses = [
            $this->getCustomerAddressMock($customerAddressId)
        ];
        $this->customerMock->expects($this->once())->method('getAddresses')->willReturn($customerAddresses);

        $this->assertEquals($this->model, $this->model->setQuoteCustomerBillingAddress($addressId));
    }

    public function testGetQuoteShippingAddressesItems()
    {
        $quoteItem = $this->getMockBuilder(AddressItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getShippingAddressesItems')->willReturn($quoteItem);
        $this->model->getQuoteShippingAddressesItems();
    }

    public function testSetShippingMethods()
    {
        $methodsArray = [1 => 'flatrate_flatrate', 2 => 'tablerate_bestway'];
        $addressId = 1;
        $addressMock = $this->getMockBuilder(QuoteAddress::class)
            ->setMethods(['getId', 'setShippingMethod'])
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock->expects($this->once())->method('getId')->willReturn($addressId);
        $this->quoteMock->expects($this->once())->method('getAllShippingAddresses')->willReturn([$addressMock]);
        $addressMock->expects($this->once())->method('setShippingMethod')->with($methodsArray[$addressId]);

        $this->mockShippingAssignment();

        //save mock
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->model->setShippingMethods($methodsArray);
    }

    /**
     * @return void
     */
    public function testCreateOrders(): void
    {
        $addressTotal = 5;
        $productType = \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;
        $infoBuyRequest = [
            'info_buyRequest' => [
                'product' => '1',
                'qty' => 1,
            ],
        ];
        $quoteItemId = 1;
        $paymentProviderCode = 'checkmo';

        $simpleProductTypeMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Simple::class)
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

        $orderAddressMock = $this->createSimpleMock(\Magento\Sales\Api\Data\OrderAddressInterface::class);
        $orderPaymentMock = $this->createSimpleMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuoteItemId'])
            ->getMock();
        $orderItemMock->method('getQuoteItemId')->willReturn($quoteItemId);
        $orderMock = $this->getOrderMock($orderAddressMock, $orderPaymentMock, $orderItemMock);

        $this->orderFactoryMock->expects($this->once())->method('create')->willReturn($orderMock);
        $this->dataObjectHelperMock->expects($this->once())->method('mergeDataObjects')
            ->with(
                \Magento\Sales\Api\Data\OrderInterface::class,
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
     * @param string $paymentProviderCode
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentMock(string $paymentProviderCode): PHPUnit_Framework_MockObject_MockObject
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
     * @param \Magento\Catalog\Model\Product\Type\Simple|PHPUnit_Framework_MockObject_MockObject $simpleProductTypeMock
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductMock($simpleProductTypeMock): PHPUnit_Framework_MockObject_MockObject
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeInstance'])
            ->getMock();
        $productMock->method('getTypeInstance')->willReturn($simpleProductTypeMock);

        return $productMock;
    }

    /**
     * @param string $productType
     * @param \Magento\Catalog\Model\Product|PHPUnit_Framework_MockObject_MockObject $productMock
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuoteItemMock($productType, $productMock): PHPUnit_Framework_MockObject_MockObject
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
     * @param \Magento\Quote\Model\Quote\Item|PHPUnit_Framework_MockObject_MockObject $quoteItemMock
     * @param string $productType
     * @param array $infoBuyRequest
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuoteAddressItemMock(
        $quoteItemMock,
        string $productType,
        array $infoBuyRequest
    ): PHPUnit_Framework_MockObject_MockObject {
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
     * @param \Magento\Quote\Model\Quote\Address\Item|PHPUnit_Framework_MockObject_MockObject $quoteAddressItemMock
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
        $shippingAddressMock->method('getShippingRateByCode')->willReturn('code');
        $shippingAddressMock->method('getCountryId')->willReturn('EN');
        $shippingAddressMock->method('getAllItems')->willReturn([$quoteAddressItemMock]);
        $shippingAddressMock->method('getAddressType')->willReturn('shipping');
        $shippingAddressMock->method('getGrandTotal')->willReturn($addressTotal);

        $billingAddressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();
        $billingAddressMock->method('validate')->willReturn(true);

        return [$shippingAddressMock, $billingAddressMock];
    }

    /**
     * @param string $paymentProviderCode
     * @param Address|PHPUnit_Framework_MockObject_MockObject $shippingAddressMock
     * @param Address|PHPUnit_Framework_MockObject_MockObject $billingAddressMock
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
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteMock->method('getId')->willReturn($quoteId);
        $this->quoteMock->method('setIsActive')->with(false)->willReturnSelf();
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderAddressInterface|PHPUnit_Framework_MockObject_MockObject $orderAddressMock
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|PHPUnit_Framework_MockObject_MockObject $orderPaymentMock
     * @param \Magento\Sales\Model\Order\Item|PHPUnit_Framework_MockObject_MockObject $orderItemMock
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getOrderMock(
        $orderAddressMock,
        $orderPaymentMock,
        $orderItemMock
    ): PHPUnit_Framework_MockObject_MockObject {
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
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
                    'getCanSendNewEmailFlag',
                    'getItems',
                    'setShippingMethod',
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
        $orderMock->method('getCanSendNewEmailFlag')->willReturn(false);
        $orderMock->method('getItems')->willReturn([$orderItemMock]);

        return $orderMock;
    }

    /**
     * Tests exception for addresses with country id not in the allowed countries list.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Some addresses can't be used due to the configurations for specific countries.
     */
    public function testCreateOrdersCountryNotPresentInAllowedListException()
    {
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

        $this->model->createOrders();
    }

    /**
     * @param ShippingAssignment $shippingAssignmentMock
     * @return CartExtension|PHPUnit_Framework_MockObject_MockObject
     */
    private function getExtensionAttributesMock(ShippingAssignment $shippingAssignmentMock)
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
     * @return ShippingAssignment | PHPUnit_Framework_MockObject_MockObject
     */
    private function getShippingAssignmentMock()
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

    private function mockShippingAssignment()
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
     * @param $customerAddressId
     * @return Address | PHPUnit_Framework_MockObject_MockObject
     */
    private function getCustomerAddressMock($customerAddressId)
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
     * @param string $className
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function createSimpleMock($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
