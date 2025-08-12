<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\InventoryApi\Test\Fixture\Source as SourceFixture;
use Magento\InventoryApi\Test\Fixture\SourceItems as SourceItemsFixture;
use Magento\InventoryApi\Test\Fixture\Stock as StockFixture;
use Magento\InventoryApi\Test\Fixture\StockSourceLinks as StockSourceLinksFixture;
use Magento\InventorySalesApi\Test\Fixture\StockSalesChannels as StockSalesChannelsFixture;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class CartItemAvailabilityTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        DbIsolation(false),
        AppIsolation(true),
        DataFixture(SourceFixture::class, as: 'source2'),
        DataFixture(StockFixture::class, as: 'stock2'),
        DataFixture(
            StockSourceLinksFixture::class,
            [
                ['stock_id' => '$stock2.stock_id$', 'source_code' => '$source2.source_code$'],
            ]
        ),
        DataFixture(
            StockSalesChannelsFixture::class,
            ['stock_id' => '$stock2.stock_id$', 'sales_channels' => ['base']]
        ),

        DataFixture(ProductFixture::class, ['sku' => 'simple1'], 'p1'),
        DataFixture(
            SourceItemsFixture::class,
            [
                ['sku' => '$p1.sku$', 'source_code' => 'default', 'quantity' => 0],
                ['sku' => '$p1.sku$', 'source_code' => '$source2.source_code$', 'quantity' => 100],
            ]
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$']),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testCartItemAvailabilityWithMSI(): void
    {
        $this->assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            [
                                'not_available_message' => null,
                                'is_available' => true,
                                'product' => [
                                    'quantity' => 100,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $this->graphQlQuery($this->getCartQuery(
                $this->fixtures->get('quoteIdMask')->getMaskedId()
            ))
        );
    }

    /**
     * Return cart query with is_available & not_available_message fields
     *
     * @param string $cartId
     * @return string
     */
    private function getCartQuery(string $cartId): string
    {
        return <<<QUERY
            {
              cart(cart_id:"{$cartId}") {
                itemsV2 {
                  items {
                    not_available_message
                    is_available
                    product {
                      quantity
                    }
                  }
                }
              }
            }
        QUERY;
    }
}
