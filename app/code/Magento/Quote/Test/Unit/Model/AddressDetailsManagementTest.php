<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model;

use Magento\Quote\Model\AddressDetailsManagement;

class AddressDetailsManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\AddressDetailsManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $billingAddressManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddressManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingMethodManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressDetailsFactory;

    protected function setUp()
    {
        $this->billingAddressManagement = $this->getMock('\Magento\Quote\Api\BillingAddressManagementInterface');
        $this->shippingAddressManagement = $this->getMock('\Magento\Quote\Api\ShippingAddressManagementInterface');
        $this->paymentMethodManagement = $this->getMock('\Magento\Quote\Api\PaymentMethodManagementInterface');
        $this->shippingMethodManagement = $this->getMock('\Magento\Quote\Api\ShippingMethodManagementInterface');
        $this->addressDetailsFactory = $this->getMock('\Magento\Quote\Model\AddressDetailsFactory', [], [], '', false);

        $this->model = new AddressDetailsManagement(
            $this->billingAddressManagement,
            $this->shippingAddressManagement,
            $this->paymentMethodManagement,
            $this->shippingMethodManagement,
            $this->addressDetailsFactory
        );
    }

    public function testSaveAddresses()
    {
        $cartId = 100;
        $billingAddressMock = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $shippingAddressMock = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');

        $this->billingAddressManagement->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock)
            ->willReturn(1);
        $this->shippingAddressManagement->expects($this->once())
            ->method('assign')
            ->with($cartId, $shippingAddressMock)
            ->willReturn(1);

        $shippingMethodMock = $this->getMock('\Magento\Quote\Api\Data\ShippingMethodInterface');
        $this->shippingMethodManagement->expects($this->once())
            ->method('getList')
            ->with($cartId)
            ->willReturn([$shippingMethodMock]);
        $paymentMethodMock = $this->getMock('\Magento\Quote\Api\Data\PaymentMethodInterface');
        $this->paymentMethodManagement->expects($this->once())
            ->method('getList')
            ->with($cartId)
            ->willReturn([$paymentMethodMock]);

        $addressDetailsMock = $this->getMock('\Magento\Quote\Model\AddressDetails', [], [], '', false);
        $this->addressDetailsFactory->expects($this->once())->method('create')->willReturn($addressDetailsMock);

        $addressDetailsMock->expects($this->once())
            ->method('setShippingMethods')
            ->with([$shippingMethodMock])
            ->willReturnSelf();
        $addressDetailsMock->expects($this->once())
            ->method('setPaymentMethods')
            ->with([$paymentMethodMock])
            ->willReturnSelf();
        $this->model->saveAddresses($cartId, $billingAddressMock, $shippingAddressMock);
    }
}
