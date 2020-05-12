<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Model\Cart\Controller;

use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Multishipping\Model\Cart\Controller\CartPlugin;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartPluginTest extends TestCase
{
    /**
     * @var CartPlugin
     */
    private $model;

    /**
     * @var MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var MockObject
     */
    private $addressRepositoryMock;

    protected function setUp(): void
    {
        $this->cartRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->addressRepositoryMock = $this->getMockForAbstractClass(AddressRepositoryInterface::class);
        $this->model = new CartPlugin(
            $this->cartRepositoryMock,
            $this->checkoutSessionMock,
            $this->addressRepositoryMock
        );
    }

    public function testBeforeDispatch()
    {
        $addressId = 100;
        $customerAddressId = 200;
        $quoteMock = $this->createPartialMock(Quote::class, [
            'isMultipleShippingAddresses',
            'getAllShippingAddresses',
            'removeAddress',
            'getShippingAddress',
            'getCustomer'
        ]);
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $addressMock = $this->createMock(Address::class);
        $addressMock->expects($this->once())->method('getId')->willReturn($addressId);

        $quoteMock->expects($this->once())->method('isMultipleShippingAddresses')->willReturn(true);
        $quoteMock->expects($this->once())->method('getAllShippingAddresses')->willReturn([$addressMock]);
        $quoteMock->expects($this->once())->method('removeAddress')->with($addressId)->willReturnSelf();

        $shippingAddressMock = $this->createMock(Address::class);
        $quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($shippingAddressMock);
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $quoteMock->expects($this->once())->method('getCustomer')->willReturn($customerMock);
        $customerMock->expects($this->once())->method('getDefaultShipping')->willReturn($customerAddressId);

        $customerAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerAddressId)
            ->willReturn($customerAddressMock);

        $shippingAddressMock->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();

        $this->cartRepositoryMock->expects($this->once())->method('save')->with($quoteMock);

        $this->model->beforeDispatch(
            $this->createMock(Cart::class),
            $this->getMockForAbstractClass(RequestInterface::class)
        );
    }
}
