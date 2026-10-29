<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Model\GuestShippingInformationManagement;
use Magento\Quote\Model\GuestCart\GetGuestCart;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestShippingInformationManagementTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ShippingInformationManagementInterface|MockObject
     */
    protected $shippingInformationManagementMock;

    /**
     * @var QuoteIdMaskFactory|MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var GuestShippingInformationManagement
     */
    protected $model;

    /**
     * @var GetGuestCart|MockObject
     */
    private $getGuestCartMock;

    protected function setUp(): void
    {
        $this->quoteIdMaskFactoryMock = $this->createMock(QuoteIdMaskFactory::class);
        $this->shippingInformationManagementMock = $this->createMock(
            ShippingInformationManagementInterface::class
        );
        $this->getGuestCartMock = $this->createMock(GetGuestCart::class);
        $this->getGuestCartMock->method('execute')->willReturn($this->createMock(Quote::class));

        $this->model = new GuestShippingInformationManagement(
            $this->quoteIdMaskFactoryMock,
            $this->shippingInformationManagementMock,
            $this->getGuestCartMock
        );
    }

    public function testSaveAddressInformation()
    {
        $cartId = 'masked_id';
        $quoteId = '100';
        $addressInformationMock = $this->createMock(ShippingInformationInterface::class);

        $quoteIdMask = $this->createPartialMockWithReflection(QuoteIdMask::class, ['load', 'getQuoteId']);
        $quoteIdMask->method('load')->willReturnSelf();
        $quoteIdMask->method('getQuoteId')->willReturn($quoteId);
        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($quoteIdMask);

        $paymentInformationMock = $this->createMock(PaymentDetailsInterface::class);
        $this->shippingInformationManagementMock->expects($this->once())
            ->method('saveAddressInformation')
            ->with(
                self::callback(fn($actualQuoteId): bool => (int) $quoteId === $actualQuoteId),
                $addressInformationMock
            )
            ->willReturn($paymentInformationMock);

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }
}
