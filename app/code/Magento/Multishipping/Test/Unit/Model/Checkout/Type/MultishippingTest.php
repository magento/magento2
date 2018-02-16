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
use Magento\Customer\Model\Data\Address;
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
use PHPUnit_Framework_TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MultishippingTest extends PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->checkoutSessionMock = $this->createSimpleMock(Session::class);
        $this->customerSessionMock = $this->createSimpleMock(CustomerSession::class);
        $orderFactoryMock = $this->createSimpleMock(OrderFactory::class);
        $eventManagerMock = $this->createSimpleMock(ManagerInterface::class);
        $scopeConfigMock = $this->createSimpleMock(ScopeConfigInterface::class);
        $sessionMock = $this->createSimpleMock(Generic::class);
        $addressFactoryMock = $this->createSimpleMock(AddressFactory::class);
        $toOrderMock = $this->createSimpleMock(ToOrder::class);
        $toOrderAddressMock = $this->createSimpleMock(ToOrderAddress::class);
        $toOrderPaymentMock = $this->createSimpleMock(ToOrderPayment::class);
        $toOrderItemMock = $this->createSimpleMock(ToOrderItem::class);
        $storeManagerMock = $this->createSimpleMock(StoreManagerInterface::class);
        $paymentSpecMock = $this->createSimpleMock(SpecificationInterface::class);
        $this->helperMock = $this->createSimpleMock(Data::class);
        $orderSenderMock = $this->createSimpleMock(OrderSender::class);
        $priceMock = $this->createSimpleMock(PriceCurrencyInterface::class);
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
        $this->shippingAssignmentProcessorMock = $this->createSimpleMock(ShippingAssignmentProcessor::class);
        $this->model = new Multishipping(
            $this->checkoutSessionMock,
            $this->customerSessionMock,
            $orderFactoryMock,
            $this->addressRepositoryMock,
            $eventManagerMock,
            $scopeConfigMock,
            $sessionMock,
            $addressFactoryMock,
            $toOrderMock,
            $toOrderAddressMock,
            $toOrderPaymentMock,
            $toOrderItemMock,
            $storeManagerMock,
            $paymentSpecMock,
            $this->helperMock,
            $orderSenderMock,
            $priceMock,
            $this->quoteRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->filterBuilderMock,
            $this->totalsCollectorMock,
            $data,
            $this->cartExtensionFactoryMock,
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
     * @expectedExceptionMessage Please check shipping address information.
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

    public function testupdateQuoteCustomerShippingAddress()
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
     * @expectedExceptionMessage Please check shipping address information.
     */
    public function testupdateQuoteCustomerShippingAddressForAddressLeak()
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
     * @expectedExceptionMessage Please check billing address information.
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
