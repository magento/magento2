<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model;

class GuestPaymentInformationManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $billingAddressManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartManagementMock;

    /**
     * @var \Magento\Checkout\Model\GuestPaymentInformationManagement
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->billingAddressManagementMock = $this->getMock(
            '\Magento\Quote\Api\GuestBillingAddressManagementInterface'
        );
        $this->paymentMethodManagementMock = $this->getMock(
            '\Magento\Quote\Api\GuestPaymentMethodManagementInterface'
        );
        $this->cartManagementMock = $this->getMock('\Magento\Quote\Api\GuestCartManagementInterface');

        $this->model = $objectManager->getObject(
            'Magento\Checkout\Model\GuestPaymentInformationManagement',
            [
                'billingAddressManagement' => $this->billingAddressManagementMock,
                'paymentMethodManagement' => $this->paymentMethodManagementMock,
                'cartManagement' => $this->cartManagementMock
            ]
        );
    }

    public function testSavePaymentInformationAndPlaceOrder()
    {
        $cartId = 100;
        $orderId = 200;
        $email = 'email@magento.com';
        $paymentMock = $this->getMock('\Magento\Quote\Api\Data\PaymentInterface');
        $billingAddressMock = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');

        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->billingAddressManagementMock->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->with($cartId)->willReturn($orderId);

        $this->assertEquals(
            $orderId,
            $this->model->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMock, $billingAddressMock)
        );
    }

    public function testSavePaymentInformation()
    {
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->getMock('\Magento\Quote\Api\Data\PaymentInterface');
        $billingAddressMock = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->billingAddressManagementMock->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);

        $this->assertTrue($this->model->savePaymentInformation($cartId, $email, $paymentMock, $billingAddressMock));
    }
}
