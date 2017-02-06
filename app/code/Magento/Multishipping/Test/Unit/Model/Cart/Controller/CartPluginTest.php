<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\Unit\Model\Cart\Controller;

class CartPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Multishipping\Model\Cart\Controller\CartPlugin
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepositoryMock;

    protected function setUp()
    {
        $this->cartRepositoryMock = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->checkoutSessionMock = $this->getMock(\Magento\Checkout\Model\Session::class, [], [], '', false);
        $this->addressRepositoryMock = $this->getMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
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
        $quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            [
                'isMultipleShippingAddresses',
                'getAllShippingAddresses',
                'removeAddress',
                'getShippingAddress',
                'getCustomer'
            ],
            [],
            '',
            false
        );
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $addressMock = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $addressMock->expects($this->once())->method('getId')->willReturn($addressId);

        $quoteMock->expects($this->once())->method('isMultipleShippingAddresses')->willReturn(true);
        $quoteMock->expects($this->once())->method('getAllShippingAddresses')->willReturn([$addressMock]);
        $quoteMock->expects($this->once())->method('removeAddress')->with($addressId)->willReturnSelf();

        $shippingAddressMock = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($shippingAddressMock);
        $customerMock = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $quoteMock->expects($this->once())->method('getCustomer')->willReturn($customerMock);
        $customerMock->expects($this->once())->method('getDefaultShipping')->willReturn($customerAddressId);

        $customerAddressMock = $this->getMock(\Magento\Customer\Api\Data\AddressInterface::class);
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
            $this->getMock(\Magento\Checkout\Controller\Cart::class, [], [], '', false),
            $this->getMock(\Magento\Framework\App\RequestInterface::class)
        );
    }
}
