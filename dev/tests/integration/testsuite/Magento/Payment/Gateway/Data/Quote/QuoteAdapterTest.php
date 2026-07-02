<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Gateway\Data\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class QuoteAdapterTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 10], 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]
        ),
    ]
    public function testGetGrandTotalAmountReturnsQuoteBaseGrandTotal(): void
    {
        $cartId = (int)$this->fixtures->get('cart')->getId();
        $quote = $this->objectManager->get(CartRepositoryInterface::class)->get($cartId);
        $quote->collectTotals();

        $adapter = $this->objectManager->create(QuoteAdapter::class, ['quote' => $quote]);

        $grandTotalAmount = $adapter->getGrandTotalAmount();

        $this->assertNotNull($grandTotalAmount);
        $this->assertGreaterThan(0, $grandTotalAmount);
        $this->assertEquals($quote->getBaseGrandTotal(), $grandTotalAmount);
    }

    /**
     * In a multi-currency store the amount and the currency code exposed to payment gateways must be a
     * consistent base-currency pair, mirroring OrderAdapter. The base grand total (not the display grand
     * total) must be returned so the amount never gets paired with a mismatching currency code.
     */
    #[
        DataFixture(ProductFixture::class, ['price' => 10], 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]
        ),
    ]
    public function testGetGrandTotalAmountReturnsBaseAmountInMultiCurrencyStore(): void
    {
        $rate = 1.5;
        $cartId = (int)$this->fixtures->get('cart')->getId();
        $quote = $this->objectManager->get(CartRepositoryInterface::class)->get($cartId);

        // Collect the real base totals (base currency), then overlay a distinct display currency.
        $quote->collectTotals();
        $baseGrandTotal = (float)$quote->getBaseGrandTotal();
        $quote->setQuoteCurrencyCode('EUR');
        $quote->setBaseToQuoteRate($rate);
        $quote->setGrandTotal($baseGrandTotal * $rate);

        // Sanity check: the quote is now genuinely multi-currency.
        $this->assertNotEquals(
            $quote->getBaseCurrencyCode(),
            $quote->getQuoteCurrencyCode(),
            'Test setup must produce a quote whose display currency differs from the base currency.'
        );
        $this->assertNotEquals(
            $baseGrandTotal,
            (float)$quote->getGrandTotal(),
            'Test setup must produce differing base and display grand totals.'
        );

        $adapter = $this->objectManager->create(QuoteAdapter::class, ['quote' => $quote]);

        // Amount is the base grand total, not the display grand total.
        $this->assertEquals($baseGrandTotal, $adapter->getGrandTotalAmount());
        $this->assertNotEquals((float)$quote->getGrandTotal(), (float)$adapter->getGrandTotalAmount());

        // Currency code is the base currency, so amount and currency stay a consistent pair.
        $this->assertEquals($quote->getBaseCurrencyCode(), $adapter->getCurrencyCode());
    }
}
