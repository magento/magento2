<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestPaymentInformationManagementTest extends TestCase
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
     * @var MockObject
     */
    protected $cartRepositoryMock;

    /**
     * @var MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var GuestPaymentInformationManagement
     */
    protected $model;

    /**
     * @var MockObject
     */
    private $loggerMock;

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
        $this->model = $objectManager->getObject(
            GuestPaymentInformationManagement::class,
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
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);

        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->with($cartId)->willReturn($orderId);

        $this->assertEquals(
            $orderId,
            $this->model->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMock, $billingAddressMock)
        );
    }

    public function testSavePaymentInformationAndPlaceOrderException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
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
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $this->getMockForAssignBillingAddress($cartId, $billingAddressMock);
        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);

        $this->assertTrue($this->model->savePaymentInformation($cartId, $email, $paymentMock, $billingAddressMock));
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
        $quoteIdMaskMock = $this->getMockBuilder(QuoteIdMask::class)
            ->addMethods(['getQuoteId'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($quoteIdMaskMock);
        $quoteIdMaskMock->expects($this->once())->method('load')->with($cartId, 'masked_id')->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->cartRepositoryMock->expects($this->once())->method('getActive')->with($cartId)->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);
        $billingAddressMock->expects($this->once())->method('setEmail')->with($email);
        $this->assertTrue($this->model->savePaymentInformation($cartId, $email, $paymentMock));
    }

    public function testSavePaymentInformationAndPlaceOrderWithLocalizedException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('DB exception');
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);

        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->method('getBillingAddress')->willReturn($billingAddressMock);
        $this->cartRepositoryMock->method('getActive')->with($cartId)->willReturn($quoteMock);

        $quoteIdMask = $this->getMockBuilder(QuoteIdMask::class)
            ->addMethods(['getQuoteId'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
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
     * @param MockObject $billingAddressMock
     * @return void
     */
    private function getMockForAssignBillingAddress(
        int $cartId,
        MockObject $billingAddressMock
    ) : void {
        $quoteIdMask = $this->getMockBuilder(QuoteIdMask::class)
            ->addMethods(['getQuoteId'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $quoteShippingAddress = $this->getMockBuilder(Address::class)
            ->addMethods(['setLimitCarrier'])
            ->onlyMethods(['getShippingMethod', 'getShippingRateByCode'])
            ->disableOriginalConstructor()
            ->getMock();
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
