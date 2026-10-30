<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class NotAvailableMessageTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        ConfigFixture('cataloginventory/options/enable_inventory_check', false, "store", "default"),
        ConfigFixture('cataloginventory/options/not_available_message', true, "store", "default"),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$product.id$',
                'qty' => 1
            ]
        )
    ]
    public function testNotAvailableMessageWithoutInventoryCheck(): void
    {
        $this->assertCartResponse();
    }

    #[
        ConfigFixture('cataloginventory/options/enable_inventory_check', true, "store", "default"),
        ConfigFixture('cataloginventory/options/not_available_message', true, "store", "default"),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$product.id$',
                'qty' => 1
            ]
        )
    ]
    public function testNotAvailableMessageWithInventoryCheck(): void
    {
        $this->assertCartResponse();
    }

    private function assertCartResponse(): void
    {
        $this->assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            [
                                'not_available_message' => null,
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId())
            )
        );
    }

    /**
     * Get cart query with not available message
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
            {
              cart(cart_id: "{$maskedQuoteId}") {
                itemsV2 {
                  items {
                    not_available_message
                  }
                }
              }
            }
        QUERY;
    }
}
