<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Data\Quote;

use Magento\Payment\Gateway\Data\Quote\QuoteAdapter;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;

/**
 * Class QuoteAdapterTest
 */
class QuoteAdapterTest extends \PHPUnit\Framework\TestCase
{
    /** @var QuoteAdapter */
    protected $model;

    /**
     * @var CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Payment\Gateway\Data\Quote\AddressAdapterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressAdapterFactoryMock;

    protected function setUp()
    {
        $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);

        $this->addressAdapterFactoryMock =
            $this->getMockBuilder(\Magento\Payment\Gateway\Data\Quote\AddressAdapterFactory::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->model = new QuoteAdapter($this->quoteMock, $this->addressAdapterFactoryMock);
    }

    public function testGetCurrencyCode()
    {
        $expected = 'USD';
        /** @var \Magento\Quote\Api\Data\CurrencyInterface $currencyMock */
        $currencyMock = $this->getMockBuilder(
            \Magento\Quote\Api\Data\CurrencyInterface::class
        )->getMockForAbstractClass();
        $currencyMock->expects($this->once())->method('getBaseCurrencyCode')->willReturn($expected);
        $this->quoteMock->expects($this->once())->method('getCurrency')->willReturn($currencyMock);
        $this->assertEquals($expected, $this->model->getCurrencyCode());
    }

    public function testGetOrderIncrementId()
    {
        $expected = '1';
        $this->quoteMock->expects($this->once())->method('getReservedOrderId')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getOrderIncrementId());
    }

    public function testGetCustomerId()
    {
        $expected = 1;
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customerMock */
        $customerMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
        )->getMockForAbstractClass();
        $customerMock->expects($this->once())->method('getId')->willReturn($expected);
        $this->quoteMock->expects($this->once())->method('getCustomer')->willReturn($customerMock);
        $this->assertEquals($expected, $this->model->getCustomerId());
    }

    public function testGetBillingAddressIsNull()
    {
        $this->quoteMock->expects($this->once())->method('getBillingAddress')->willReturn(null);

        $this->assertSame(null, $this->model->getBillingAddress());
    }

    public function testGetBillingAddress()
    {
        /** @var AddressAdapterInterface $addressAdapterMock */
        $addressAdapterMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\AddressAdapterInterface::class)
            ->getMockForAbstractClass();
        /** @var \Magento\Quote\Api\Data\AddressInterface $quoteAddressMock */
        $quoteAddressMock = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();
        $this->addressAdapterFactoryMock->expects($this->once())
            ->method('create')
            ->with(['address' => $quoteAddressMock])
            ->willReturn($addressAdapterMock);
        $this->quoteMock->expects($this->exactly(2))->method('getBillingAddress')->willReturn($quoteAddressMock);

        $this->assertSame($addressAdapterMock, $this->model->getBillingAddress());
    }

    public function testGetShippingAddressIsNull()
    {
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn(null);

        $this->assertSame(null, $this->model->getShippingAddress());
    }

    public function testGetShippingAddress()
    {
        /** @var AddressAdapterInterface $addressAdapterMock */
        $addressAdapterMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\AddressAdapterInterface::class)
            ->getMockForAbstractClass();
        /** @var \Magento\Quote\Api\Data\AddressInterface $quoteAddressMock */
        $quoteAddressMock = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();
        $this->addressAdapterFactoryMock->expects($this->once())
            ->method('create')
            ->with(['address' => $quoteAddressMock])
            ->willReturn($addressAdapterMock);
        $this->quoteMock->expects($this->exactly(2))->method('getShippingAddress')->willReturn($quoteAddressMock);

        $this->assertSame($addressAdapterMock, $this->model->getShippingAddress());
    }
}
