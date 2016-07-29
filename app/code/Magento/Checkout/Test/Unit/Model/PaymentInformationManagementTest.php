<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model;

use Magento\Framework\Exception\CouldNotSaveException;

class PaymentInformationManagementTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Checkout\Model\PaymentInformationManagement
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->billingAddressManagementMock = $this->getMock(
            '\Magento\Quote\Api\BillingAddressManagementInterface'
        );
        $this->paymentMethodManagementMock = $this->getMock(
            '\Magento\Quote\Api\PaymentMethodManagementInterface'
        );
        $this->cartManagementMock = $this->getMock('\Magento\Quote\Api\CartManagementInterface');

        $this->model = $objectManager->getObject(
            'Magento\Checkout\Model\PaymentInformationManagement',
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
        $paymentMock = $this->getMock('\Magento\Quote\Api\Data\PaymentInterface');
        $billingAddressMock = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');

        $this->billingAddressManagementMock->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->with($cartId)->willReturn($orderId);

        $this->assertEquals(
            $orderId,
            $this->model->savePaymentInformationAndPlaceOrder($cartId, $paymentMock, $billingAddressMock)
        );
    }

    /**
     * @expectedExceptionMessage An error occurred on the server. Please try to place the order again.
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSavePaymentInformationAndPlaceOrderException()
    {
        $cartId = 100;
        $paymentMock = $this->getMock('\Magento\Quote\Api\Data\PaymentInterface');
        $billingAddressMock = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');

        $this->billingAddressManagementMock->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $exception = new CouldNotSaveException(__('DB exception'));
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $paymentMock, $billingAddressMock);
    }

    public function testSavePaymentInformationAndPlaceOrderIfBillingAddressNotExist()
    {
        $cartId = 100;
        $orderId = 200;
        $paymentMock = $this->getMock('\Magento\Quote\Api\Data\PaymentInterface');

        $this->billingAddressManagementMock->expects($this->never())->method('assign');
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->with($cartId)->willReturn($orderId);

        $this->assertEquals(
            $orderId,
            $this->model->savePaymentInformationAndPlaceOrder($cartId, $paymentMock)
        );
    }

    public function testSavePaymentInformation()
    {
        $cartId = 100;
        $paymentMock = $this->getMock('\Magento\Quote\Api\Data\PaymentInterface');
        $billingAddressMock = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');

        $this->billingAddressManagementMock->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);

        $this->assertTrue($this->model->savePaymentInformation($cartId, $paymentMock, $billingAddressMock));
    }

    public function testSavePaymentInformationWithoutBillingAddress()
    {
        $cartId = 100;
        $paymentMock = $this->getMock('\Magento\Quote\Api\Data\PaymentInterface');

        $this->billingAddressManagementMock->expects($this->never())->method('assign');
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);

        $this->assertTrue($this->model->savePaymentInformation($cartId, $paymentMock));
    }
}
