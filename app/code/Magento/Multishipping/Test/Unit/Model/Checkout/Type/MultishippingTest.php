<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\Unit\Model\Checkout\Type;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MultishippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsCollectorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cartExtensionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    protected function setUp()
    {
        $this->checkoutSessionMock = $this->getMock(\Magento\Checkout\Model\Session::class, [], [], '', false);
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $orderFactoryMock = $this->getMock(\Magento\Sales\Model\OrderFactory::class, [], [], '', false);
        $eventManagerMock = $this->getMock(\Magento\Framework\Event\ManagerInterface::class, [], [], '', false);
        $scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class, [], [], '', false);
        $sessionMock = $this->getMock(\Magento\Framework\Session\Generic::class, [], [], '', false);
        $addressFactoryMock = $this->getMock(\Magento\Quote\Model\Quote\AddressFactory::class, [], [], '', false);
        $toOrderMock = $this->getMock(\Magento\Quote\Model\Quote\Address\ToOrder::class, [], [], '', false);
        $toOrderAddressMock =
            $this->getMock(\Magento\Quote\Model\Quote\Address\ToOrderAddress::class, [], [], '', false);
        $toOrderPaymentMock =
            $this->getMock(\Magento\Quote\Model\Quote\Payment\ToOrderPayment::class, [], [], '', false);
        $toOrderItemMock = $this->getMock(\Magento\Quote\Model\Quote\Item\ToOrderItem::class, [], [], '', false);
        $storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class, [], [], '', false);
        $paymentSpecMock =
            $this->getMock(\Magento\Payment\Model\Method\SpecificationInterface::class, [], [], '', false);
        $this->helperMock = $this->getMock(\Magento\Multishipping\Helper\Data::class, [], [], '', false);
        $orderSenderMock =
            $this->getMock(\Magento\Sales\Model\Order\Email\Sender\OrderSender::class, [], [], '', false);
        $priceMock = $this->getMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class, [], [], '', false);
        $this->quoteRepositoryMock = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->filterBuilderMock = $this->getMock(\Magento\Framework\Api\FilterBuilder::class, [], [], '', false);
        $this->searchCriteriaBuilderMock = $this->getMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            [],
            [],
            '',
            false
        );
        $this->addressRepositoryMock = $this->getMock(
            \Magento\Customer\Api\AddressRepositoryInterface::class,
            [],
            [],
            '',
            false
        );
        /** This is used to get past _init() which is called in construct. */
        $data['checkout_session'] = $this->checkoutSessionMock;
        $this->quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->customerMock = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class, [], [], '', false);
        $this->customerMock->expects($this->atLeastOnce())->method('getId')->willReturn(null);
        $this->checkoutSessionMock->expects($this->atLeastOnce())->method('getQuote')->willReturn($this->quoteMock);
        $this->customerSessionMock->expects($this->atLeastOnce())->method('getCustomerDataObject')
            ->willReturn($this->customerMock);
        $this->totalsCollectorMock = $this->getMock(
            \Magento\Quote\Model\Quote\TotalsCollector::class,
            [],
            [],
            '',
            false
        );
        $this->model = new \Magento\Multishipping\Model\Checkout\Type\Multishipping(
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
            $data
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cartExtensionFactoryMock = $this->getMock(
            \Magento\Quote\Api\Data\CartExtensionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->shippingAssignmentProcessorMock = $this->getMock(
            \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor::class,
            [],
            [],
            '',
            false
        );
        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'cartExtensionFactory',
            $this->cartExtensionFactoryMock
        );
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

        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteria::class, [], [], '', false);
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())->method('addFilters')->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())->method('create')
            ->willReturn($searchCriteriaMock);

        $resultMock = $this->getMock(
            \Magento\Customer\Api\Data\AddressSearchResultsInterface::class,
            [],
            [],
            '',
            false
        );
        $this->addressRepositoryMock->expects($this->atLeastOnce())->method('getList')->willReturn($resultMock);
        $addressItemMock = $this->getMock(\Magento\Customer\Api\Data\AddressInterface::class, [], [], '', false);
        $resultMock->expects($this->atLeastOnce())->method('getItems')->willReturn([$addressItemMock]);
        $addressItemMock->expects($this->atLeastOnce())->method('getId')->willReturn(null);

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

        $customerAddressMock = $this->getMock(\Magento\Customer\Model\Data\Address::class, [], [], '', false);
        $customerAddressMock->expects($this->atLeastOnce())->method('getId')->willReturn($customerAddressId);
        $customerAddresses = [$customerAddressMock];

        $quoteItemMock = $this->getMock(\Magento\Quote\Model\Quote\Item::class, [], [], '', false);
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

        $customerAddressMock = $this->getMock(\Magento\Customer\Model\Data\Address::class, [], [], '', false);
        $customerAddressMock->expects($this->atLeastOnce())->method('getId')->willReturn($customerAddressId);
        $customerAddresses = [$customerAddressMock];
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

        $customerAddressMock = $this->getMock(\Magento\Customer\Model\Data\Address::class, [], [], '', false);
        $customerAddressMock->expects($this->atLeastOnce())->method('getId')->willReturn($customerAddressId);
        $customerAddresses = [$customerAddressMock];
        $this->customerMock->expects($this->once())->method('getAddresses')->willReturn($customerAddresses);

        $this->assertEquals($this->model, $this->model->updateQuoteCustomerShippingAddress($addressId));
    }

    public function testSetQuoteCustomerBillingAddress()
    {
        $addressId = 42;
        $customerAddressId = 42;

        $customerAddressMock = $this->getMock(\Magento\Customer\Model\Data\Address::class, [], [], '', false);
        $customerAddressMock->expects($this->atLeastOnce())->method('getId')->willReturn($customerAddressId);
        $customerAddresses = [$customerAddressMock];
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

        $customerAddressMock = $this->getMock(\Magento\Customer\Model\Data\Address::class, [], [], '', false);
        $customerAddressMock->expects($this->atLeastOnce())->method('getId')->willReturn($customerAddressId);
        $customerAddresses = [$customerAddressMock];
        $this->customerMock->expects($this->once())->method('getAddresses')->willReturn($customerAddresses);

        $this->assertEquals($this->model, $this->model->setQuoteCustomerBillingAddress($addressId));
    }

    public function testGetQuoteShippingAddressesItems()
    {
        $quoteItem = $this->getMock(\Magento\Quote\Model\Quote\Address\Item::class, [], [], '', false);
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getShippingAddressesItems')->willReturn($quoteItem);
        $this->model->getQuoteShippingAddressesItems();
    }

    public function testSetShippingMethods()
    {

        $methodsArray = [1 => 'flatrate_flatrate', 2 => 'tablerate_bestway'];
        $addressId = 1;
        $addressMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['getId', 'setShippingMethod'],
            [],
            '',
            false
        );
        $extensionAttributesMock = $this->getMock(
            \Magento\Quote\Api\Data\CartExtension::class,
            ['setShippingAssignments'],
            [],
            '',
            false
        );
        $shippingAssignmentMock = $this->getMock(
            \Magento\Quote\Model\ShippingAssignment::class,
            ['getShipping', 'setShipping'],
            [],
            '',
            false
        );
        $shippingMock = $this->getMock(\Magento\Quote\Model\Shipping::class, ['setMethod'], [], '', false);

        $addressMock->expects($this->once())->method('getId')->willReturn($addressId);
        $this->quoteMock->expects($this->once())->method('getAllShippingAddresses')->willReturn([$addressMock]);
        $addressMock->expects($this->once())->method('setShippingMethod')->with($methodsArray[$addressId]);
        //mock for prepareShippingAssignment
        $this->quoteMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesMock);
        $this->shippingAssignmentProcessorMock
            ->expects($this->once())
            ->method('create')
            ->with($this->quoteMock)
            ->willReturn($shippingAssignmentMock);
        $shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn($shippingMock);
        $shippingMock->expects($this->once())->method('setMethod')->with(null)->willReturnSelf();
        $shippingAssignmentMock->expects($this->once())->method('setShipping')->with($shippingMock);
        $extensionAttributesMock
            ->expects($this->once())
            ->method('setShippingAssignments')
            ->with([$shippingAssignmentMock])
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('setExtensionAttributes')->with($extensionAttributesMock);
        //save mock
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->model->setShippingMethods($methodsArray);
    }
}
