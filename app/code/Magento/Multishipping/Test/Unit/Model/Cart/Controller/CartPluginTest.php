<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\Unit\Model\Cart\Controller;

class CartPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Multishipping\Model\Cart\Controller\CartPlugin
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $addressRepositoryMock;

    protected function setUp(): void
    {
        $this->cartRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->addressRepositoryMock = $this->createMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $this->model = new \Magento\Multishipping\Model\Cart\Controller\CartPlugin(
            $this->cartRepositoryMock,
            $this->checkoutSessionMock,
            $this->addressRepositoryMock
        );
    }

    public function testBeforeDispatch()
    {
        $addressId = 100;
        $customerAddressId = 200;
        $quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, [
                'isMultipleShippingAddresses',
                'getAllShippingAddresses',
                'removeAddress',
                'getShippingAddress',
                'getCustomer'
            ]);
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $addressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $addressMock->expects($this->once())->method('getId')->willReturn($addressId);

        $quoteMock->expects($this->once())->method('isMultipleShippingAddresses')->willReturn(true);
        $quoteMock->expects($this->once())->method('getAllShippingAddresses')->willReturn([$addressMock]);
        $quoteMock->expects($this->once())->method('removeAddress')->with($addressId)->willReturnSelf();

        $shippingAddressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($shippingAddressMock);
        $customerMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $quoteMock->expects($this->once())->method('getCustomer')->willReturn($customerMock);
        $customerMock->expects($this->once())->method('getDefaultShipping')->willReturn($customerAddressId);

        $customerAddressMock = $this->createMock(\Magento\Customer\Api\Data\AddressInterface::class);
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
            $this->createMock(\Magento\Checkout\Controller\Cart::class),
            $this->createMock(\Magento\Framework\App\RequestInterface::class)
        );
    }
}
