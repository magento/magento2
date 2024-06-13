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
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Test\Unit\Helper\QuoteIdMaskLoadByMaskedIdTestHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestShippingInformationManagementTest extends TestCase
{
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

    protected function setUp(): void
    {
        $this->quoteIdMaskFactoryMock = $this->createMock(QuoteIdMaskFactory::class);
        $this->shippingInformationManagementMock = $this->createMock(
            ShippingInformationManagementInterface::class
        );
        $this->model = new GuestShippingInformationManagement(
            $this->quoteIdMaskFactoryMock,
            $this->shippingInformationManagementMock
        );
    }

    public function testSaveAddressInformation()
    {
        $cartId = 'masked_id';
        $quoteId = '100';
        $addressInformationMock = $this->createMock(ShippingInformationInterface::class);

        $quoteIdMask = new QuoteIdMaskLoadByMaskedIdTestHelper($quoteId);
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
