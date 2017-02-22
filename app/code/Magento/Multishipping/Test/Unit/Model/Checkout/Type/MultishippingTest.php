<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\Unit\Model\Checkout\Type;

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

    protected function setUp()
    {
        $this->checkoutSessionMock = $this->getMock('\Magento\Checkout\Model\Session', [], [], '', false);
        $this->customerSessionMock = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $orderFactoryMock = $this->getMock('\Magento\Sales\Model\OrderFactory', [], [], '', false);
        $eventManagerMock = $this->getMock('\Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $sessionMock = $this->getMock('\Magento\Framework\Session\Generic', [], [], '', false);
        $addressFactoryMock = $this->getMock('\Magento\Quote\Model\Quote\AddressFactory', [], [], '', false);
        $toOrderMock = $this->getMock('\Magento\Quote\Model\Quote\Address\ToOrder', [], [], '', false);
        $toOrderAddressMock = $this->getMock('\Magento\Quote\Model\Quote\Address\ToOrderAddress', [], [], '', false);
        $toOrderPaymentMock = $this->getMock('\Magento\Quote\Model\Quote\Payment\ToOrderPayment', [], [], '', false);
        $toOrderItemMock = $this->getMock('\Magento\Quote\Model\Quote\Item\ToOrderItem', [], [], '', false);
        $storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $paymentSpecMock = $this->getMock('\Magento\Payment\Model\Method\SpecificationInterface', [], [], '', false);
        $this->helperMock = $this->getMock('\Magento\Multishipping\Helper\Data', [], [], '', false);
        $orderSenderMock = $this->getMock('\Magento\Sales\Model\Order\Email\Sender\OrderSender', [], [], '', false);
        $priceMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface', [], [], '', false);
        $quoteRepositoryMock = $this->getMock('\Magento\Quote\Api\CartRepositoryInterface');
        $this->filterBuilderMock = $this->getMock('\Magento\Framework\Api\FilterBuilder', [], [], '', false);
        $this->searchCriteriaBuilderMock = $this->getMock(
            '\Magento\Framework\Api\SearchCriteriaBuilder',
            [],
            [],
            '',
            false
        );
        $this->addressRepositoryMock = $this->getMock(
            '\Magento\Customer\Api\AddressRepositoryInterface',
            [],
            [],
            '',
            false
        );

        /**
         * This is used to get past _init() which is called in construct.
         */
        $data['checkout_session'] = $this->checkoutSessionMock;
        $this->quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->customerMock = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);
        $this->customerMock->expects($this->atLeastOnce())->method('getId')->willReturn(null);
        $this->checkoutSessionMock->expects($this->atLeastOnce())->method('getQuote')->willReturn($this->quoteMock);
        $this->customerSessionMock->expects($this->atLeastOnce())->method('getCustomerDataObject')
            ->willReturn($this->customerMock);
        $this->totalsCollectorMock = $this->getMock('Magento\Quote\Model\Quote\TotalsCollector', [], [], '', false);
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
            $quoteRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->filterBuilderMock,
            $this->totalsCollectorMock,
            $data
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

        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())->method('addFilters')->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())->method('create')
            ->willReturn($searchCriteriaMock);

        $resultMock = $this->getMock('\Magento\Customer\Api\Data\AddressSearchResultsInterface', [], [], '', false);
        $this->addressRepositoryMock->expects($this->atLeastOnce())->method('getList')->willReturn($resultMock);
        $addressItemMock = $this->getMock('\Magento\Customer\Api\Data\AddressInterface', [], [], '', false);
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

        $customerAddressMock = $this->getMock('\Magento\Customer\Model\Data\Address', [], [], '', false);
        $customerAddressMock->expects($this->atLeastOnce())->method('getId')->willReturn($customerAddressId);
        $customerAddresses = [$customerAddressMock];

        $quoteItemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', [], [], '', false);
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

        $customerAddressMock = $this->getMock('\Magento\Customer\Model\Data\Address', [], [], '', false);
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

        $customerAddressMock = $this->getMock('\Magento\Customer\Model\Data\Address', [], [], '', false);
        $customerAddressMock->expects($this->atLeastOnce())->method('getId')->willReturn($customerAddressId);
        $customerAddresses = [$customerAddressMock];
        $this->customerMock->expects($this->once())->method('getAddresses')->willReturn($customerAddresses);

        $this->assertEquals($this->model, $this->model->updateQuoteCustomerShippingAddress($addressId));
    }

    public function testSetQuoteCustomerBillingAddress()
    {
        $addressId = 42;
        $customerAddressId = 42;

        $customerAddressMock = $this->getMock('\Magento\Customer\Model\Data\Address', [], [], '', false);
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

        $customerAddressMock = $this->getMock('\Magento\Customer\Model\Data\Address', [], [], '', false);
        $customerAddressMock->expects($this->atLeastOnce())->method('getId')->willReturn($customerAddressId);
        $customerAddresses = [$customerAddressMock];
        $this->customerMock->expects($this->once())->method('getAddresses')->willReturn($customerAddresses);

        $this->assertEquals($this->model, $this->model->setQuoteCustomerBillingAddress($addressId));
    }

    public function testGetQuoteShippingAddressesItems()
    {
        $quoteItem = $this->getMock('Magento\Quote\Model\Quote\Address\Item', [], [], '', false);
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getShippingAddressesItems')->willReturn($quoteItem);
        $this->model->getQuoteShippingAddressesItems();
    }
}
