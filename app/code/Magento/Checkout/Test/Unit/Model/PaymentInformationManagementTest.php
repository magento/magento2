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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->billingAddressManagementMock = $this->getMock(
            \Magento\Quote\Api\BillingAddressManagementInterface::class
        );
        $this->paymentMethodManagementMock = $this->getMock(
            \Magento\Quote\Api\PaymentMethodManagementInterface::class
        );
        $this->cartManagementMock = $this->getMock(\Magento\Quote\Api\CartManagementInterface::class);

        $this->loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);

        $this->model = $objectManager->getObject(
            \Magento\Checkout\Model\PaymentInformationManagement::class,
            [
                'billingAddressManagement' => $this->billingAddressManagementMock,
                'paymentMethodManagement' => $this->paymentMethodManagementMock,
                'cartManagement' => $this->cartManagementMock
            ]
        );
        $objectManager->setBackwardCompatibleProperty($this->model, 'logger', $this->loggerMock);
    }

    public function testSavePaymentInformationAndPlaceOrder()
    {
        $cartId = 100;
        $orderId = 200;
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->getMock(\Magento\Quote\Api\Data\AddressInterface::class);

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
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->getMock(\Magento\Quote\Api\Data\AddressInterface::class);

        $this->billingAddressManagementMock->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $exception = new \Exception(__('DB exception'));
        $this->loggerMock->expects($this->once())->method('critical');
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $paymentMock, $billingAddressMock);
    }

    public function testSavePaymentInformationAndPlaceOrderIfBillingAddressNotExist()
    {
        $cartId = 100;
        $orderId = 200;
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);

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
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->getMock(\Magento\Quote\Api\Data\AddressInterface::class);

        $this->billingAddressManagementMock->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);

        $this->assertTrue($this->model->savePaymentInformation($cartId, $paymentMock, $billingAddressMock));
    }

    public function testSavePaymentInformationWithoutBillingAddress()
    {
        $cartId = 100;
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);

        $this->billingAddressManagementMock->expects($this->never())->method('assign');
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);

        $this->assertTrue($this->model->savePaymentInformation($cartId, $paymentMock));
    }

    /**
     * @expectedExceptionMessage DB exception
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSavePaymentInformationAndPlaceOrderWithLocolizedException()
    {
        $cartId = 100;
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->getMock(\Magento\Quote\Api\Data\AddressInterface::class);

        $this->billingAddressManagementMock->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $phrase = new \Magento\Framework\Phrase(__('DB exception'));
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->loggerMock->expects($this->never())->method('critical');
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $paymentMock, $billingAddressMock);
    }
}
