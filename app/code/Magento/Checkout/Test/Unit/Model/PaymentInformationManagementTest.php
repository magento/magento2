<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepositoryMock;

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
        $this->cartRepositoryMock = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)->getMock();
        $this->model = $objectManager->getObject(
            \Magento\Checkout\Model\PaymentInformationManagement::class,
            [
                'billingAddressManagement' => $this->billingAddressManagementMock,
                'paymentMethodManagement' => $this->paymentMethodManagementMock,
                'cartManagement' => $this->cartManagementMock
            ]
        );
        $objectManager->setBackwardCompatibleProperty($this->model, 'logger', $this->loggerMock);
        $objectManager->setBackwardCompatibleProperty($this->model, 'cartRepository', $this->cartRepositoryMock);
    }

    public function testSavePaymentInformationAndPlaceOrder()
    {
        $cartId = 100;
        $orderId = 200;
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->getMock(\Magento\Quote\Api\Data\AddressInterface::class);

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
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

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
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

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);

        $this->assertTrue($this->model->savePaymentInformation($cartId, $paymentMock, $billingAddressMock));
    }

    public function testSavePaymentInformationWithoutBillingAddress()
    {
        $cartId = 100;
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);

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

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);

        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $phrase = new \Magento\Framework\Phrase(__('DB exception'));
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->loggerMock->expects($this->never())->method('critical');
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $paymentMock, $billingAddressMock);
    }

    /**
     * @param int $cartId
     * @param \PHPUnit_Framework_MockObject_MockObject $billingAddressMock
     */
    private function getMockForAssignBillingAddress($cartId, $billingAddressMock)
    {
        $billingAddressId = 1;
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $quoteBillingAddress = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $quoteShippingAddress = $this->getMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['setLimitCarrier', 'getShippingMethod'],
            [],
            '',
            false
        );
        $this->cartRepositoryMock->expects($this->any())->method('getActive')->with($cartId)->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($quoteBillingAddress);
        $quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($quoteShippingAddress);
        $quoteBillingAddress->expects($this->once())->method('getId')->willReturn($billingAddressId);
        $quoteMock->expects($this->once())->method('removeAddress')->with($billingAddressId);
        $quoteMock->expects($this->once())->method('setBillingAddress')->with($billingAddressMock);
        $quoteMock->expects($this->once())->method('setDataChanges')->willReturnSelf();
        $quoteShippingAddress->expects($this->any())->method('getShippingMethod')->willReturn('flatrate_flatrate');
        $quoteShippingAddress->expects($this->once())->method('setLimitCarrier')->with('flatrate')->willReturnSelf();
    }
}
