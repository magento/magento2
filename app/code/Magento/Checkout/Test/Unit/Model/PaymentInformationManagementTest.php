<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentInformationManagementTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $billingAddressManagementMock;

    /**
     * @var MockObject
     */
    protected $paymentMethodManagementMock;

    /**
     * @var MockObject
     */
    protected $cartManagementMock;

    /**
     * @var PaymentInformationManagement
     */
    protected $model;

    /**
     * @var MockObject
     */
    private $loggerMock;

    /**
     * @var MockObject
     */
    private $cartRepositoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->billingAddressManagementMock = $this->createMock(
            BillingAddressManagementInterface::class
        );
        $this->paymentMethodManagementMock = $this->createMock(
            PaymentMethodManagementInterface::class
        );
        $this->cartManagementMock = $this->getMockForAbstractClass(CartManagementInterface::class);

        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->cartRepositoryMock = $this->getMockBuilder(CartRepositoryInterface::class)
            ->getMock();
        $this->model = $objectManager->getObject(
            PaymentInformationManagement::class,
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
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->with($cartId)->willReturn($orderId);

        $this->assertEquals(
            $orderId,
            $this->model->savePaymentInformationAndPlaceOrder($cartId, $paymentMock, $billingAddressMock)
        );
    }

    public function testSavePaymentInformationAndPlaceOrderException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $cartId = 100;
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $exception = new \Exception('DB exception');
        $this->loggerMock->expects($this->once())->method('critical');
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $paymentMock, $billingAddressMock);

        $this->expectExceptionMessage(
            'A server error stopped your order from being placed. Please try to place your order again.'
        );
    }

    public function testSavePaymentInformationAndPlaceOrderIfBillingAddressNotExist()
    {
        $cartId = 100;
        $orderId = 200;
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);

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
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);

        $this->assertTrue($this->model->savePaymentInformation($cartId, $paymentMock, $billingAddressMock));
    }

    public function testSavePaymentInformationWithoutBillingAddress()
    {
        $cartId = 100;
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);

        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);

        $this->assertTrue($this->model->savePaymentInformation($cartId, $paymentMock));
    }

    public function testSavePaymentInformationAndPlaceOrderWithLocolizedException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('DB exception');
        $cartId = 100;
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);

        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $phrase = new Phrase(__('DB exception'));
        $exception = new LocalizedException($phrase);
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $paymentMock, $billingAddressMock);
    }

    /**
     * Test for save payment and place order with new billing address
     *
     * @return void
     */
    public function testSavePaymentInformationAndPlaceOrderWithNewBillingAddress(): void
    {
        $cartId = 100;
        $quoteBillingAddressId = 1;
        $customerId = 1;
        $quoteMock = $this->createMock(Quote::class);
        $quoteBillingAddress = $this->createMock(Address::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);

        $quoteBillingAddress->method('getCustomerId')->willReturn($customerId);
        $quoteMock->method('getBillingAddress')->willReturn($quoteBillingAddress);
        $quoteBillingAddress->method('getId')->willReturn($quoteBillingAddressId);
        $this->cartRepositoryMock->method('getActive')->with($cartId)->willReturn($quoteMock);

        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $billingAddressMock->expects($this->once())->method('setCustomerId')->with($customerId);
        $this->assertTrue($this->model->savePaymentInformation($cartId, $paymentMock, $billingAddressMock));
    }

    /**
     * @param int $cartId
     * @param MockObject $billingAddressMock
     */
    private function getMockForAssignBillingAddress($cartId, $billingAddressMock)
    {
        $billingAddressId = 1;
        $quoteMock = $this->createMock(Quote::class);
        $quoteBillingAddress = $this->createMock(Address::class);
        $shippingRate = $this->createPartialMock(Rate::class, []);
        $shippingRate->setCarrier('flatrate');
        $quoteShippingAddress = $this->getMockBuilder(Address::class)
            ->addMethods(['setLimitCarrier'])
            ->onlyMethods(['getShippingMethod', 'getShippingRateByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartRepositoryMock->expects($this->any())->method('getActive')->with($cartId)->willReturn($quoteMock);
        $quoteMock->method('getBillingAddress')->willReturn($quoteBillingAddress);
        $quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($quoteShippingAddress);
        $quoteBillingAddress->expects($this->once())->method('getId')->willReturn($billingAddressId);
        $quoteBillingAddress->expects($this->once())->method('getId')->willReturn($billingAddressId);
        $quoteMock->expects($this->once())->method('removeAddress')->with($billingAddressId);
        $quoteMock->expects($this->once())->method('setBillingAddress')->with($billingAddressMock);
        $quoteMock->expects($this->once())->method('setDataChanges')->willReturnSelf();
        $quoteShippingAddress->expects($this->any())->method('getShippingRateByCode')->willReturn($shippingRate);
        $quoteShippingAddress->expects($this->any())->method('getShippingMethod')->willReturn('flatrate_flatrate');
        $quoteShippingAddress->expects($this->once())->method('setLimitCarrier')->with('flatrate')->willReturnSelf();
    }
}
