<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Cart;

use Magento\Directory\Model\Currency;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\UpdateCartCurrency;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateCartCurrencyTest extends TestCase
{
    /**
     * @var CartRepositoryInterface|MockObject
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var StoreRepositoryInterface|MockObject
     */
    private StoreRepositoryInterface $storeRepository;

    /**
     * @var UpdateCartCurrency
     */
    private UpdateCartCurrency $model;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->storeRepository = $this->createMock(StoreRepositoryInterface::class);
        $this->model = new UpdateCartCurrency($this->cartRepository, $this->storeRepository);
    }

    /**
     * When the cart is moved to a different store in the same website, the currency code (a string)
     * must be stored on the quote - not the Currency object returned by getCurrentCurrency().
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteSetsStringCurrencyCodeWhenStoreChanges(): void
    {
        $cartStoreId = 1;
        $newStoreId = 2;
        $website = $this->createStub(Website::class);
        $cartStore = $this->createStore('USD', $website);
        $newStore = $this->createStore('EUR', $website);
        $this->storeRepository->method('getById')
            ->willReturnMap([
                [$cartStoreId, $cartStore],
                [$newStoreId, $newStore],
            ]);
        $cart = $this->createQuote($cartStoreId);
        $cart->setId(10);
        $savedCart = $this->createMock(Quote::class);
        $this->cartRepository->expects($this->once())->method('save')->with($cart);
        $this->cartRepository->expects($this->once())
            ->method('get')
            ->with(10)
            ->willReturn($savedCart);
        $this->assertSame($savedCart, $this->model->execute($cart, $newStoreId));
        $this->assertSame($newStoreId, $cart->getStoreId());
        $this->assertSame('EUR', $cart->getStoreCurrencyCode());
        $this->assertSame('EUR', $cart->getQuoteCurrencyCode());
    }

    /**
     * Moving a cart to a store in a different website is not allowed.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteThrowsWhenWebsiteDiffers(): void
    {
        $cartStoreId = 1;
        $newStoreId = 2;
        $cartStore = $this->createStore('USD', $this->createStub(Website::class));
        $newStore = $this->createStore('EUR', $this->createStub(Website::class));
        $this->storeRepository->method('getById')
            ->willReturnMap([
                [$cartStoreId, $cartStore],
                [$newStoreId, $newStore],
            ]);
        $cart = $this->createQuote($cartStoreId);
        $this->cartRepository->expects($this->never())->method('save');
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Can\'t assign cart to store in different website.');
        $this->model->execute($cart, $newStoreId);
    }

    /**
     * Same store, but the quote currency drifted from the store currency: the quote currency code
     * (a string) must be re-applied from the store.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteResyncsStringQuoteCurrencyCodeForSameStore(): void
    {
        $storeId = 1;
        $cartStore = $this->createStore('USD', $this->createStub(Website::class));
        $this->storeRepository->method('getById')->with($storeId)->willReturn($cartStore);
        $cart = $this->createQuote($storeId);
        $cart->setQuoteCurrencyCode('EUR');
        $cart->setId(10);
        $savedCart = $this->createMock(Quote::class);
        $this->cartRepository->expects($this->once())->method('save')->with($cart);
        $this->cartRepository->expects($this->once())
            ->method('get')
            ->with(10)
            ->willReturn($savedCart);
        $this->assertSame($savedCart, $this->model->execute($cart, $storeId));
        $this->assertSame('USD', $cart->getQuoteCurrencyCode());
    }

    /**
     * Same store and matching currency: the cart is returned unchanged and never persisted.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteReturnsCartUnchangedWhenCurrencyMatches(): void
    {
        $storeId = 1;
        $cartStore = $this->createStore('USD', $this->createStub(Website::class));
        $this->storeRepository->method('getById')->with($storeId)->willReturn($cartStore);
        $cart = $this->createQuote($storeId);
        $cart->setQuoteCurrencyCode('USD');
        $this->cartRepository->expects($this->never())->method('save');
        $this->assertSame($cart, $this->model->execute($cart, $storeId));
        $this->assertSame('USD', $cart->getQuoteCurrencyCode());
    }

    /**
     * Builds a Quote with real behavior but without running its constructor
     *
     * @param int $storeId
     * @return Quote
     */
    private function createQuote(int $storeId): Quote
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $quote->setStoreId($storeId);
        return $quote;
    }

    /**
     * Builds a Store mock whose current currency exposes the given code.
     *
     * @param string $currencyCode
     * @param Website|MockObject $website
     * @return Store|MockObject
     */
    private function createStore(string $currencyCode, $website): Store
    {
        $currency = $this->createStub(Currency::class);
        $currency->method('getCode')->willReturn($currencyCode);
        $store = $this->createStub(Store::class);
        $store->method('getCurrentCurrency')->willReturn($currency);
        $store->method('getWebsite')->willReturn($website);
        return $store;
    }
}
