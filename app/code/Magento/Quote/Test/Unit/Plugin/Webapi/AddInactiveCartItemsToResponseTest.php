<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Plugin\Webapi;

use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Plugin\Webapi\AddInactiveCartItemsToResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class AddInactiveCartItemsToResponseTest extends TestCase
{
    /**
     * @var ServiceOutputProcessor&Stub
     */
    private $outputProcessor;

    /**
     * @var AddInactiveCartItemsToResponse
     */
    private AddInactiveCartItemsToResponse $plugin;

    protected function setUp(): void
    {
        $this->outputProcessor = $this->createStub(ServiceOutputProcessor::class);
        $this->plugin = new AddInactiveCartItemsToResponse();
    }

    public static function otherServiceCallsProvider(): array
    {
        return [
            'cart repository getList' => [CartRepositoryInterface::class, 'getList'],
            'cart repository getActive' => [CartRepositoryInterface::class, 'getActive'],
            'guest cart repository get' => [GuestCartRepositoryInterface::class, 'get'],
        ];
    }

    public function testAssignsVisibleItemsToInactiveCart(): void
    {
        $items = [$this->createStub(Item::class), $this->createStub(Item::class)];
        $quote = $this->createMock(Quote::class);
        $quote->method('getIsActive')->willReturn(false);
        $quote->method('getItems')->willReturn(null);
        $quote->method('getAllVisibleItems')->willReturn($items);
        $quote->expects($this->once())->method('setItems')->with($items);

        $this->plugin->beforeProcess($this->outputProcessor, $quote, CartRepositoryInterface::class, 'get');
    }

    public function testLeavesActiveCartUntouched(): void
    {
        $quote = $this->createMock(Quote::class);
        $quote->method('getIsActive')->willReturn(true);
        $quote->expects($this->never())->method('getAllVisibleItems');
        $quote->expects($this->never())->method('setItems');

        $this->plugin->beforeProcess($this->outputProcessor, $quote, CartRepositoryInterface::class, 'get');
    }

    public function testKeepsAlreadyAssignedItems(): void
    {
        $quote = $this->createMock(Quote::class);
        $quote->method('getIsActive')->willReturn(false);
        $quote->method('getItems')->willReturn([$this->createStub(Item::class)]);
        $quote->expects($this->never())->method('getAllVisibleItems');
        $quote->expects($this->never())->method('setItems');

        $this->plugin->beforeProcess($this->outputProcessor, $quote, CartRepositoryInterface::class, 'get');
    }

    #[DataProvider('otherServiceCallsProvider')]
    public function testIgnoresOtherServiceCalls(string $serviceClassName, string $serviceMethodName): void
    {
        $quote = $this->createMock(Quote::class);
        $quote->method('getIsActive')->willReturn(false);
        $quote->method('getItems')->willReturn(null);
        $quote->expects($this->never())->method('getAllVisibleItems');
        $quote->expects($this->never())->method('setItems');

        $this->plugin->beforeProcess($this->outputProcessor, $quote, $serviceClassName, $serviceMethodName);
    }

    public function testIgnoresNonQuoteResult(): void
    {
        $cart = $this->createMock(CartInterface::class);
        $cart->expects($this->never())->method('setItems');

        $this->plugin->beforeProcess($this->outputProcessor, $cart, CartRepositoryInterface::class, 'get');
    }
}
