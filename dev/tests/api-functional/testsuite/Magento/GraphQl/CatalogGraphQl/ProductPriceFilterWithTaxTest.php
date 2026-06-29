<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogGraphQl;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Indexer\Test\Fixture\Indexer;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Verify GraphQL price filter uses displayed (incl. tax) values when catalog prices exclude tax.
 */
#[
    DataFixture(
        TaxRateFixture::class,
        [
            'tax_country_id' => 'US',
            'tax_region_id' => '12',
            'tax_postcode' => '*',
            'rate' => '7.5',
        ],
        'tax_rate'
    ),
    DataFixture(
        TaxRuleFixture::class,
        [
            'customer_tax_class_ids' => [3],
            'product_tax_class_ids' => [2],
            'tax_rate_ids' => ['$tax_rate.id$'],
        ],
        'tax_rule'
    ),
    DataFixture(CategoryFixture::class, as: 'category'),
    DataFixture(
        ProductFixture::class,
        [
            'sku' => 'graphql_price_filter_a',
            'price' => 9.30,
            'category_ids' => ['$category.id$'],
        ],
        'product_a'
    ),
    DataFixture(
        ProductFixture::class,
        [
            'sku' => 'graphql_price_filter_b',
            'price' => 10.00,
            'category_ids' => ['$category.id$'],
        ],
        'product_b'
    ),
    DataFixture(Indexer::class),
]
class ProductPriceFilterWithTaxTest extends GraphQlAbstract
{
    private const QUERY = <<<'QUERY'
{
  products(
    filter: {
      price: {from: %s, to: %s}
      category_uid: {eq: "%s"}
    }
    sort: {price: ASC}
  ) {
    items {
      sku
      price_range {
        minimum_price {
          final_price {
            value
          }
        }
      }
    }
  }
}
QUERY;

    #[
        Config('tax/calculation/price_includes_tax', 0, ScopeInterface::SCOPE_STORE, 'default'),
        Config('tax/display/type', 2, ScopeInterface::SCOPE_STORE, 'default'),
        Config('tax/defaults/country', 'US', ScopeInterface::SCOPE_STORE, 'default'),
        Config('tax/defaults/region', '12', ScopeInterface::SCOPE_STORE, 'default'),
        Config('tax/defaults/postcode', '*', ScopeInterface::SCOPE_STORE, 'default'),
        Config('shipping/origin/country_id', 'US', ScopeInterface::SCOPE_STORE, 'default'),
        Config('shipping/origin/region_id', '12', ScopeInterface::SCOPE_STORE, 'default'),
        Config('shipping/origin/postcode', '90001', ScopeInterface::SCOPE_STORE, 'default'),
    ]
    public function testPriceFilterIncludesProductWithDisplayedPriceAtLowerBound(): void
    {
        $response = $this->graphQlQuery(
            sprintf(self::QUERY, '10', '15', $this->getCategoryUid())
        );

        self::assertCount(2, $response['products']['items']);
        self::assertSame(
            'graphql_price_filter_a',
            $response['products']['items'][0]['sku']
        );
        self::assertEquals(
            10.7,
            $response['products']['items'][0]['price_range']['minimum_price']['final_price']['value']
        );
        self::assertSame(
            'graphql_price_filter_b',
            $response['products']['items'][1]['sku']
        );
    }

    #[
        Config('tax/calculation/price_includes_tax', 0, ScopeInterface::SCOPE_STORE, 'default'),
        Config('tax/display/type', 2, ScopeInterface::SCOPE_STORE, 'default'),
        Config('tax/defaults/country', 'US', ScopeInterface::SCOPE_STORE, 'default'),
        Config('tax/defaults/region', '12', ScopeInterface::SCOPE_STORE, 'default'),
        Config('tax/defaults/postcode', '*', ScopeInterface::SCOPE_STORE, 'default'),
        Config('shipping/origin/country_id', 'US', ScopeInterface::SCOPE_STORE, 'default'),
        Config('shipping/origin/region_id', '12', ScopeInterface::SCOPE_STORE, 'default'),
        Config('shipping/origin/postcode', '90001', ScopeInterface::SCOPE_STORE, 'default'),
    ]
    public function testPriceFilterExcludesProductBelowDisplayedFromValue(): void
    {
        $response = $this->graphQlQuery(
            sprintf(self::QUERY, '10', '10.74', $this->getCategoryUid())
        );

        self::assertCount(1, $response['products']['items']);
        self::assertSame(
            'graphql_price_filter_a',
            $response['products']['items'][0]['sku']
        );
        self::assertEquals(
            10.7,
            $response['products']['items'][0]['price_range']['minimum_price']['final_price']['value']
        );
    }

    #[
        Config('tax/calculation/price_includes_tax', 0, ScopeInterface::SCOPE_STORE, 'default'),
        Config('tax/display/type', 2, ScopeInterface::SCOPE_STORE, 'default'),
        Config('tax/defaults/country', 'US', ScopeInterface::SCOPE_STORE, 'default'),
        Config('tax/defaults/region', '12', ScopeInterface::SCOPE_STORE, 'default'),
        Config('tax/defaults/postcode', '*', ScopeInterface::SCOPE_STORE, 'default'),
        Config('shipping/origin/country_id', 'US', ScopeInterface::SCOPE_STORE, 'default'),
        Config('shipping/origin/region_id', '12', ScopeInterface::SCOPE_STORE, 'default'),
        Config('shipping/origin/postcode', '90001', ScopeInterface::SCOPE_STORE, 'default'),
    ]
    public function testPriceFilterExcludesProductAboveDisplayedToValue(): void
    {
        $response = $this->graphQlQuery(
            sprintf(self::QUERY, '10', '10.7', $this->getCategoryUid())
        );

        self::assertCount(1, $response['products']['items']);
        self::assertSame(
            'graphql_price_filter_a',
            $response['products']['items'][0]['sku']
        );
    }

    private function getCategoryUid(): string
    {
        $category = DataFixtureStorageManager::getStorage()->get('category');

        return base64_encode((string)$category->getId());
    }
}
