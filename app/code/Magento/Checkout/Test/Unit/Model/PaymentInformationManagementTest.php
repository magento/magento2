<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Api\Exception\PaymentProcessingRateLimitExceededException;
use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Checkout\Api\PaymentSavingRateLimiterInterface;
use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Checkout\Model\PaymentDetailsFactory;
use Magento\Checkout\Model\AddressComparatorInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\QuoteAddressValidationService;

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

    /**
     * @var PaymentProcessingRateLimiterInterface|MockObject
     */
    private $rateLimiterMock;

    /**
     * @var PaymentSavingRateLimiterInterface|MockObject
     */
    private $saveLimiterMock;

    /**
     * @var Address|MockObject
     */
    private $quoteShippingAddress;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private $addressRepositoryMock;

    protected function setUp(): void
    {
        $this->billingAddressManagementMock = $this->createMock(
            BillingAddressManagementInterface::class
        );
        $this->paymentMethodManagementMock = $this->createMock(
            PaymentMethodManagementInterface::class
        );
        $this->cartManagementMock = $this->createMock(CartManagementInterface::class);

        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->rateLimiterMock = $this->createMock(PaymentProcessingRateLimiterInterface::class);
        $this->saveLimiterMock = $this->createMock(PaymentSavingRateLimiterInterface::class);
        $this->addressRepositoryMock = $this->createMock(AddressRepositoryInterface::class);
        $paymentDetailsFactoryMock = $this->createMock(PaymentDetailsFactory::class);
        $cartTotalsRepositoryMock = $this->createMock(CartTotalRepositoryInterface::class);
        $addressComparatorMock = $this->createMock(AddressComparatorInterface::class);
        $quoteAddressValidationServiceMock = $this->createMock(QuoteAddressValidationService::class);

        $this->model = new PaymentInformationManagement(
            $this->billingAddressManagementMock,
            $this->paymentMethodManagementMock,
            $this->cartManagementMock,
            $paymentDetailsFactoryMock,
            $cartTotalsRepositoryMock,
            $this->rateLimiterMock,
            $this->saveLimiterMock,
            $this->cartRepositoryMock,
            $this->addressRepositoryMock,
            $addressComparatorMock,
            $this->loggerMock,
            $quoteAddressValidationServiceMock
        );

        $this->quoteShippingAddress = $this->createMock(Address::class);
    }

    public function testSavePaymentInformationAndPlaceOrder()
    {
        $shippingAddressMock = $this->createMock(CustomerAddressInterface::class);
        $this->quoteShippingAddress->expects($this->once())
            ->method('getSaveInAddressBook')
            ->willReturn(true);
        $this->quoteShippingAddress->expects($this->once())
            ->method('getSameAsBilling')
            ->willReturn(true);
        $this->quoteShippingAddress->expects($this->once())
            ->method('exportCustomerAddress')
            ->willReturn($shippingAddressMock);

        $orderId = 200;
        $this->assertEquals(
            $orderId,
            $this->placeOrder($orderId)
        );
    }

    /**
     * Valdiate that the method is rate-limited.
     *
     * @return void
     */
    public function testSavePaymentInformationAndPlaceOrderLimited(): void
    {
        $this->rateLimiterMock->method('limit')
            ->willThrowException(new PaymentProcessingRateLimitExceededException(__('Error')));
        $this->expectException(PaymentProcessingRateLimitExceededException::class);

        $this->placeOrder();
    }

    public function testSavePaymentInformationAndPlaceOrderException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $cartId = 100;
        $paymentMock = $this->createMock(PaymentInterface::class);
        $billingAddressMock = $this->createMock(AddressInterface::class);

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->any())->method('set')->with($cartId, $paymentMock);
        $exception = new \Exception('DB exception');
        $this->loggerMock->expects($this->any())->method('critical');
        $this->cartManagementMock->expects($this->any())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $paymentMock, $billingAddressMock);

        $this->expectExceptionMessage(
            'A server error stopped your order from being placed. Please try to place your order again.'
        );
    }

    public function testSavePaymentInformationAndPlaceOrderIfBillingAddressNotExist()
    {
        $cartId = 100;
        $orderId = 200;
        $paymentMock = $this->createMock(PaymentInterface::class);

        $this->paymentMethodManagementMock->expects($this->any())->method('set')->with($cartId, $paymentMock);
        $this->cartManagementMock->expects($this->any())->method('placeOrder')->with($cartId)->willReturn($orderId);

        $this->assertEquals(
            $orderId,
            $this->model->savePaymentInformationAndPlaceOrder($cartId, $paymentMock)
        );
    }

    public function testSavePaymentInformation()
    {
        $this->assertTrue($this->savePayment());
    }

    /**
     * Validate that the method is rate-limited.
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
        $paymentMock = $this->createMock(PaymentInterface::class);

        $this->paymentMethodManagementMock->expects($this->any())->method('set')->with($cartId, $paymentMock);

        $this->assertTrue($this->model->savePaymentInformation($cartId, $paymentMock));
    }

    public function testSavePaymentInformationAndPlaceOrderWithLocolizedException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('DB exception');
        $cartId = 100;
        $paymentMock = $this->createMock(PaymentInterface::class);
        $billingAddressMock = $this->createMock(AddressInterface::class);

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);

        $this->paymentMethodManagementMock->expects($this->any())->method('set')->with($cartId, $paymentMock);
        $phrase = new Phrase(__('DB exception'));
        $exception = new LocalizedException($phrase);
        $this->cartManagementMock->expects($this->any())->method('placeOrder')->willThrowException($exception);

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
        $billingAddressMock = $this->createMock(AddressInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $quoteBillingAddress->method('getCustomerId')->willReturn($customerId);
        $quoteMock->method('getBillingAddress')->willReturn($quoteBillingAddress);
        $quoteBillingAddress->method('getId')->willReturn($quoteBillingAddressId);
        $this->cartRepositoryMock->method('getActive')->with($cartId)->willReturn($quoteMock);

        $this->paymentMethodManagementMock->expects($this->any())->method('set')->with($cartId, $paymentMock);
        $billingAddressMock->expects($this->any())->method('setCustomerId')->with($customerId);
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
        $customerMock = $this->createMock(CustomerInterface::class);
        $quoteBillingAddress = $this->createMock(Address::class);
        $shippingRate = $this->createMock(Rate::class);
        $this->cartRepositoryMock->expects($this->any())->method('getActive')->with($cartId)->willReturn($quoteMock);
        $quoteMock->method('getBillingAddress')->willReturn($quoteBillingAddress);
        $quoteMock->method('getCustomer')->willReturn($customerMock);
        $quoteMock->method('getShippingAddress')->willReturn($this->quoteShippingAddress);
        $quoteBillingAddress->method('getId')->willReturn($billingAddressId);
        $quoteBillingAddress->method('getId')->willReturn($billingAddressId);
        $quoteMock->expects($this->any())->method('removeAddress')->with($billingAddressId);
        $quoteMock->expects($this->any())->method('setBillingAddress')->with($billingAddressMock);
        $quoteMock->expects($this->any())->method('setDataChanges')->willReturnSelf();
        $this->quoteShippingAddress->method('getShippingRateByCode')->willReturn($shippingRate);
        $this->quoteShippingAddress->method('getShippingMethod')->willReturn(null);
    }

    /**
     * Save payment information.
     *
     * @return mixed
     */
    private function savePayment()
    {
        $cartId = 100;
        $paymentMock = $this->createMock(PaymentInterface::class);
        $billingAddressMock = $this->createMock(AddressInterface::class);

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->any())->method('set')->with($cartId, $paymentMock);

        return $this->model->savePaymentInformation($cartId, $paymentMock, $billingAddressMock);
    }

    /**
     * Call `place order`.
     *
     * @param int|null $orderId
     * @return mixed
     */
    private function placeOrder(?int $orderId = 200)
    {
        $cartId = 100;
        $paymentMock = $this->createMock(PaymentInterface::class);
        $billingAddressMock = $this->createMock(AddressInterface::class);

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->any())->method('set')->with($cartId, $paymentMock);
        $this->cartManagementMock->expects($this->any())->method('placeOrder')->with($cartId)->willReturn($orderId);

        return $this->model->savePaymentInformationAndPlaceOrder($cartId, $paymentMock, $billingAddressMock);
    }
}
