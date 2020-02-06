<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteIdMask;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestPaymentInformationManagementTest extends \PHPUnit\Framework\TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var \Magento\Checkout\Model\GuestPaymentInformationManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->billingAddressManagementMock = $this->createMock(
            \Magento\Quote\Api\GuestBillingAddressManagementInterface::class
        );
        $this->paymentMethodManagementMock = $this->createMock(
            \Magento\Quote\Api\GuestPaymentMethodManagementInterface::class
        );
        $this->cartManagementMock = $this->createMock(\Magento\Quote\Api\GuestCartManagementInterface::class);
        $this->cartRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);

        $this->quoteIdMaskFactoryMock = $this->createPartialMock(
            \Magento\Quote\Model\QuoteIdMaskFactory::class,
            ['create']
        );
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->model = $objectManager->getObject(
            \Magento\Checkout\Model\GuestPaymentInformationManagement::class,
            [
                'billingAddressManagement' => $this->billingAddressManagementMock,
                'paymentMethodManagement' => $this->paymentMethodManagementMock,
                'cartManagement' => $this->cartManagementMock,
                'cartRepository' => $this->cartRepositoryMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock
            ]
        );
        $objectManager->setBackwardCompatibleProperty($this->model, 'logger', $this->loggerMock);
    }

    public function testSavePaymentInformationAndPlaceOrder()
    {
        $cartId = 100;
        $orderId = 200;
        $email = 'email@magento.com';
        $paymentMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);

        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->with($cartId)->willReturn($orderId);

        $this->assertEquals(
            $orderId,
            $this->model->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMock, $billingAddressMock)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSavePaymentInformationAndPlaceOrderException()
    {
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);

        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__('DB exception'));
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMock, $billingAddressMock);

        $this->expectExceptionMessage(
            'A server error stopped your order from being placed. Please try to place your order again.'
        );
    }

    public function testSavePaymentInformation()
    {
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);

        $this->assertTrue($this->model->savePaymentInformation($cartId, $email, $paymentMock, $billingAddressMock));
    }

    public function testSavePaymentInformationWithoutBillingAddress()
    {
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
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
     * @expectedExceptionMessage DB exception
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSavePaymentInformationAndPlaceOrderWithLocalizedException()
    {
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);

        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->method('getBillingAddress')->willReturn($billingAddressMock);
        $this->cartRepositoryMock->method('getActive')->with($cartId)->willReturn($quoteMock);

        $quoteIdMask = $this->createPartialMock(QuoteIdMask::class, ['getQuoteId', 'load']);
        $this->quoteIdMaskFactoryMock->method('create')->willReturn($quoteIdMask);
        $quoteIdMask->method('load')->with($cartId, 'masked_id')->willReturnSelf();
        $quoteIdMask->method('getQuoteId')->willReturn($cartId);

        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $phrase = new \Magento\Framework\Phrase(__('DB exception'));
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMock, $billingAddressMock);
    }

    /**
     * @param int $cartId
     * @param \PHPUnit_Framework_MockObject_MockObject $billingAddressMock
     * @return void
     */
    private function getMockForAssignBillingAddress(
        int $cartId,
        \PHPUnit_Framework_MockObject_MockObject $billingAddressMock
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
        $shippingRate = $this->createPartialMock(\Magento\Quote\Model\Quote\Address\Rate::class, []);
        $shippingRate->setCarrier('flatrate');
        $quoteShippingAddress = $this->createPartialMock(
            Address::class,
            ['setLimitCarrier', 'getShippingMethod', 'getShippingRateByCode']
        );
        $this->cartRepositoryMock->method('getActive')
            ->with($cartId)
            ->willReturn($quote);
        $quote->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($quoteBillingAddress);
        $quote->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($quoteShippingAddress);
        $quoteBillingAddress->expects($this->once())
            ->method('getId')
            ->willReturn($billingAddressId);
        $quote->expects($this->once())
            ->method('removeAddress')
            ->with($billingAddressId);
        $quote->expects($this->once())
            ->method('setBillingAddress')
            ->with($billingAddressMock);
        $quoteShippingAddress->expects($this->any())
            ->method('getShippingRateByCode')
            ->willReturn($shippingRate);
        $quote->expects($this->once())
            ->method('setDataChanges')
            ->willReturnSelf();
        $quoteShippingAddress->method('getShippingMethod')
            ->willReturn('flatrate_flatrate');
        $quoteShippingAddress->expects($this->once())
            ->method('setLimitCarrier')
            ->with('flatrate');
    }
}
