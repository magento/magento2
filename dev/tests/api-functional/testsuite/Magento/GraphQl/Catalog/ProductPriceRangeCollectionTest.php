<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogRule\Test\Fixture\Rule as CatalogRuleFixture;
use Magento\Customer\Model\Group;
use Magento\Indexer\Test\Fixture\Indexer;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tier prices and catalog rule prices loaded for the whole product list are applied to the right product
 */
class ProductPriceRangeCollectionTest extends GraphQlAbstract
{
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'price-range-collection-1',
                'price' => 100,
                'tier_prices' => [['customer_group_id' => Group::NOT_LOGGED_IN_ID, 'qty' => 1, 'value' => 80]],
            ],
            'p1'
        ),
        DataFixture(ProductFixture::class, ['sku' => 'price-range-collection-2', 'price' => 50], 'p2'),
        DataFixture(ProductFixture::class, ['sku' => 'price-range-collection-3', 'price' => 30], 'p3'),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
                'conditions' => [['attribute' => 'sku', 'value' => 'price-range-collection-2']],
                'website_ids' => [1],
                'customer_group_ids' => [Group::NOT_LOGGED_IN_ID],
            ],
            'rule'
        ),
        DataFixture(Indexer::class),
    ]
    public function testEachProductGetsItsOwnTierAndCatalogRulePrice(): void
    {
        $query = <<<QUERY
{
  products(
    filter: {sku: {in: ["price-range-collection-1", "price-range-collection-2", "price-range-collection-3"]}}
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
        $response = $this->graphQlQuery($query);

        $finalPrices = [];
        foreach ($response['products']['items'] as $item) {
            $finalPrices[$item['sku']] = $item['price_range']['minimum_price']['final_price']['value'];
        }
        ksort($finalPrices);

        $this->assertEquals(
            [
                'price-range-collection-1' => 80,
                'price-range-collection-2' => 45,
                'price-range-collection-3' => 30,
            ],
            $finalPrices
        );
    }
}
