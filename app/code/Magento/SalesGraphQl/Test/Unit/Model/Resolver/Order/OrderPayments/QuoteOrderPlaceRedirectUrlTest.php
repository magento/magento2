<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Test\Unit\Model\Resolver\Order\OrderPayments;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\Resolver\Order\OrderPayments\QuoteOrderPlaceRedirectUrl;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteOrderPlaceRedirectUrlTest extends TestCase
{
    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @var CartInterface|MockObject
     */
    private $quoteMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var QuoteOrderPlaceRedirectUrl|MockObject
     */
    private $quoteOrderPlaceRedirectUrl;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @var QuoteRepository|MockObject
     */
    private $quoteRepositoryMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);

        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentMock->method('getOrderPlaceRedirectUrl')->willReturn('https://example.com/payment');

        $this->quoteMock = $this->createMock(CartInterface::class);
        $this->quoteMock->method('getEntityId')->willReturn(123);
        $this->quoteMock->method('getId')->willReturn(123);
        $this->quoteMock->method('getQuoteId')->willReturn(456);
        $this->quoteMock->method('getPayment')->willReturn($this->paymentMock);

        $this->orderMock = $this->createMock(OrderInterface::class);
        $this->orderMock->method('getEntityId')->willReturn(123);
        $this->orderMock->method('getQuoteId')->willReturn(456);

        $this->quoteRepositoryMock = $this->createMock(QuoteRepository::class);
        $this->quoteRepositoryMock->method('get')->willReturn($this->quoteMock);
        $this->quoteOrderPlaceRedirectUrl = new QuoteOrderPlaceRedirectUrl($this->quoteRepositoryMock);
    }

    public function testResolve(): void
    {
        $fieldMock = $this->createMock(Field::class);
        $resolveInfoMock = $this->createMock(ResolveInfo::class);
        $value = ['model' => $this->orderMock];
        $args = [];

        $this->paymentMock->expects($this->atMost(1))
            ->method('getOrderPlaceRedirectUrl')
            ->willReturn('https://example.com/payment');

        $result = $this->quoteOrderPlaceRedirectUrl->resolve($fieldMock, $this->contextMock, $resolveInfoMock, $value, $args);
        $this->assertEquals('https://example.com/payment', $result);
    }

    public function testResolveNoUrl(): void
    {
        $fieldMock = $this->createMock(Field::class);
        $resolveInfoMock = $this->createMock(ResolveInfo::class);
        $value = ['model' => $this->orderMock];
        $args = [];

        $this->paymentMock->expects($this->atMost(1))
            ->method('getOrderPlaceRedirectUrl')
            ->willReturn('');

        $result = $this->quoteOrderPlaceRedirectUrl->resolve($fieldMock, $this->contextMock, $resolveInfoMock, $value, $args);
        $this->assertNull($result);
    }

    public function testResolveNoQuote(): void
    {
        $fieldMock = $this->createMock(Field::class);
        $resolveInfoMock = $this->createMock(ResolveInfo::class);
        $value = ['model' => $this->orderMock];
        $args = [];

        $this->quoteMock->method('getQuoteId')->willReturn(null);

        $result = $this->quoteOrderPlaceRedirectUrl->resolve($fieldMock, $this->contextMock, $resolveInfoMock, $value, $args);
        $this->assertNull($result);
    }

    public function testResolveThrowsExceptionForMissingModelValue(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');
        $value = ['model' => null];
        $args = [];

        $this->quoteOrderPlaceRedirectUrl->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $value, $args);
    }
}
