<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Data\Quote;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\Quote\AddressAdapterFactory;
use Magento\Payment\Gateway\Data\Quote\QuoteAdapter;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CurrencyInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteAdapterTest extends TestCase
{
    /** @var QuoteAdapter */
    protected $model;

    /**
     * @var CartInterface|MockObject
     */
    protected $quoteMock;

    /**
     * @var AddressAdapterFactory|MockObject
     */
    protected $addressAdapterFactoryMock;

    protected function setUp(): void
    {
        $this->quoteMock = $this->createMock(Quote::class);

        $this->addressAdapterFactoryMock =
            $this->getMockBuilder(AddressAdapterFactory::class)
                ->onlyMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->model = new QuoteAdapter($this->quoteMock, $this->addressAdapterFactoryMock);
    }

    public function testGetCurrencyCode()
    {
        $expected = 'USD';
        /** @var CurrencyInterface $currencyMock */
        $currencyMock = $this->getMockBuilder(
            CurrencyInterface::class
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
        /** @var CustomerInterface $customerMock */
        $customerMock = $this->getMockBuilder(
            CustomerInterface::class
        )->getMockForAbstractClass();
        $customerMock->expects($this->once())->method('getId')->willReturn($expected);
        $this->quoteMock->expects($this->once())->method('getCustomer')->willReturn($customerMock);
        $this->assertEquals($expected, $this->model->getCustomerId());
    }

    public function testGetBillingAddressIsNull()
    {
        $this->quoteMock->expects($this->once())->method('getBillingAddress')->willReturn(null);

        $this->assertNull($this->model->getBillingAddress());
    }

    public function testGetBillingAddress()
    {
        /** @var AddressAdapterInterface $addressAdapterMock */
        $addressAdapterMock = $this->getMockBuilder(AddressAdapterInterface::class)
            ->getMockForAbstractClass();
        /** @var AddressInterface $quoteAddressMock */
        $quoteAddressMock = $this->getMockBuilder(AddressInterface::class)
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

        $this->assertNull($this->model->getShippingAddress());
    }

    public function testGetShippingAddress()
    {
        /** @var AddressAdapterInterface $addressAdapterMock */
        $addressAdapterMock = $this->getMockBuilder(AddressAdapterInterface::class)
            ->getMockForAbstractClass();
        /** @var AddressInterface $quoteAddressMock */
        $quoteAddressMock = $this->getMockBuilder(AddressInterface::class)
            ->getMockForAbstractClass();
        $this->addressAdapterFactoryMock->expects($this->once())
            ->method('create')
            ->with(['address' => $quoteAddressMock])
            ->willReturn($addressAdapterMock);
        $this->quoteMock->expects($this->exactly(2))->method('getShippingAddress')->willReturn($quoteAddressMock);

        $this->assertSame($addressAdapterMock, $this->model->getShippingAddress());
    }
}
