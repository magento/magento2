<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Api\Exception\PaymentProcessingRateLimitExceededException;
use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Checkout\Api\PaymentSavingRateLimiterInterface;
use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\GuestBillingAddressManagementInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestPaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestPaymentInformationManagementTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $billingAddressManagementMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentMethodManagementMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartManagementMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var GuestPaymentInformationManagement
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var PaymentProcessingRateLimiterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $limiterMock;

    /**
     * @var PaymentSavingRateLimiterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saveLimiterMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->billingAddressManagementMock = $this->createMock(
            GuestBillingAddressManagementInterface::class
        );
        $this->paymentMethodManagementMock = $this->createMock(
            GuestPaymentMethodManagementInterface::class
        );
        $this->cartManagementMock = $this->getMockForAbstractClass(GuestCartManagementInterface::class);
        $this->cartRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);

        $this->quoteIdMaskFactoryMock = $this->createPartialMock(
            QuoteIdMaskFactory::class,
            ['create']
        );
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->limiterMock = $this->getMockForAbstractClass(PaymentProcessingRateLimiterInterface::class);
        $this->saveLimiterMock = $this->getMockForAbstractClass(PaymentSavingRateLimiterInterface::class);
        $this->model = $objectManager->getObject(
            GuestPaymentInformationManagement::class,
            [
                'billingAddressManagement' => $this->billingAddressManagementMock,
                'paymentMethodManagement' => $this->paymentMethodManagementMock,
                'cartManagement' => $this->cartManagementMock,
                'cartRepository' => $this->cartRepositoryMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
                'paymentsRateLimiter' => $this->limiterMock,
                'savingRateLimiter' => $this->saveLimiterMock
            ]
        );
        $objectManager->setBackwardCompatibleProperty($this->model, 'logger', $this->loggerMock);
    }

    public function testSavePaymentInformationAndPlaceOrder()
    {
        $orderId = 200;
        $this->assertEquals($orderId, $this->placeOrder($orderId));
    }

    /**
     * Validate that "testSavePaymentInformationAndPlaceOrderLimited" calls are limited.
     *
     * @return void
     */
    public function testSavePaymentInformationAndPlaceOrderLimited(): void
    {
        $this->expectException(PaymentProcessingRateLimitExceededException::class);
        $this->limiterMock->method('limit')
            ->willThrowException(new PaymentProcessingRateLimitExceededException(__('Error')));
        $this->placeOrder();
    }

    /**
     */
    public function testSavePaymentInformationAndPlaceOrderException()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);

        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $exception = new CouldNotSaveException(__('DB exception'));
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMock, $billingAddressMock);

        $this->expectExceptionMessage(
            'A server error stopped your order from being placed. Please try to place your order again.'
        );
    }

    public function testSavePaymentInformation()
    {
        $this->assertTrue($this->savePayment());
    }

    /**
     * Validate that this method is rate-limited.
     *
     * @return void
     */
    public function testSavePaymentInformationLimited(): void
    {
        $this->saveLimiterMock->method('limit')
            ->willThrowException(new PaymentProcessingRateLimitExceededException(__('Error')));

        $this->assertFalse($this->savePayment());
    }

    public function testSavePaymentInformationWithoutBillingAddress()
    {
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $quoteMock = $this->createMock(Quote::class);

        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->billingAddressManagementMock->expects($this->never())->method('assign');
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $quoteIdMaskMock = $this->createPartialMock(QuoteIdMask::class, ['getQuoteId', 'load']);
        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($quoteIdMaskMock);
        $quoteIdMaskMock->expects($this->once())->method('load')->with($cartId, 'masked_id')->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->cartRepositoryMock->expects($this->once())->method('getActive')->with($cartId)->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);
        $billingAddressMock->expects($this->once())->method('setEmail')->with($email);
        $this->assertTrue($this->model->savePaymentInformation($cartId, $email, $paymentMock));
    }

    /**
     */
    public function testSavePaymentInformationAndPlaceOrderWithLocalizedException()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage('DB exception');

        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);

        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->method('getBillingAddress')->willReturn($billingAddressMock);
        $this->cartRepositoryMock->method('getActive')->with($cartId)->willReturn($quoteMock);

        $quoteIdMask = $this->createPartialMock(QuoteIdMask::class, ['getQuoteId', 'load']);
        $this->quoteIdMaskFactoryMock->method('create')->willReturn($quoteIdMask);
        $quoteIdMask->method('load')->with($cartId, 'masked_id')->willReturnSelf();
        $quoteIdMask->method('getQuoteId')->willReturn($cartId);

        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $phrase = new Phrase(__('DB exception'));
        $exception = new LocalizedException($phrase);
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMock, $billingAddressMock);
    }

    /**
     * @param int $cartId
     * @param \PHPUnit\Framework\MockObject\MockObject $billingAddressMock
     * @return void
     */
    private function getMockForAssignBillingAddress(
        int $cartId,
        \PHPUnit\Framework\MockObject\MockObject $billingAddressMock
    ) : void {
        $quoteIdMask = $this->createPartialMock(QuoteIdMask::class, ['getQuoteId', 'load']);
        $this->quoteIdMaskFactoryMock->method('create')
            ->willReturn($quoteIdMask);
        $quoteIdMask->method('load')
            ->with($cartId, 'masked_id')
            ->willReturnSelf();
        $quoteIdMask->method('getQuoteId')
            ->willReturn($cartId);

        $billingAddressId = 1;
        $quote = $this->createMock(Quote::class);
        $quoteBillingAddress = $this->createMock(Address::class);
        $shippingRate = $this->createPartialMock(Rate::class, []);
        $shippingRate->setCarrier('flatrate');
        $quoteShippingAddress = $this->createPartialMock(
            Address::class,
            ['setLimitCarrier', 'getShippingMethod', 'getShippingRateByCode']
        );
        $this->cartRepositoryMock->method('getActive')
            ->with($cartId)
            ->willReturn($quote);
        $quote->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($quoteBillingAddress);
        $quote->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($quoteShippingAddress);
        $quoteBillingAddress->expects($this->any())
            ->method('getId')
            ->willReturn($billingAddressId);
        $quote->expects($this->any())
            ->method('removeAddress')
            ->with($billingAddressId);
        $quote->expects($this->any())
            ->method('setBillingAddress')
            ->with($billingAddressMock);
        $quoteShippingAddress->expects($this->any())
            ->method('getShippingRateByCode')
            ->willReturn($shippingRate);
        $quote->expects($this->any())
            ->method('setDataChanges')
            ->willReturnSelf();
        $quoteShippingAddress->method('getShippingMethod')
            ->willReturn('flatrate_flatrate');
        $quoteShippingAddress->expects($this->any())
            ->method('setLimitCarrier')
            ->with('flatrate');
    }

    /**
     * Place order.
     *
     * @param int $orderId
     * @return mixed Method call result.
     */
    private function placeOrder(?int $orderId = 200)
    {
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);

        $billingAddressMock->expects($this->any())->method('setEmail')->with($email)->willReturnSelf();

        $this->paymentMethodManagementMock->expects($this->any())->method('set')->with($cartId, $paymentMock);
        $this->cartManagementMock->expects($this->any())
            ->method('placeOrder')
            ->with($cartId)
            ->willReturn($orderId);

        return $this->model->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMock, $billingAddressMock);
    }

    /**
     * Save payment information.
     *
     * @return mixed Call result.
     */
    private function savePayment()
    {
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
        $billingAddressMock->expects($this->any())->method('setEmail')->with($email)->willReturnSelf();

        $this->paymentMethodManagementMock->expects($this->any())->method('set')->with($cartId, $paymentMock);

        return $this->model->savePaymentInformation($cartId, $email, $paymentMock, $billingAddressMock);
    }
}
