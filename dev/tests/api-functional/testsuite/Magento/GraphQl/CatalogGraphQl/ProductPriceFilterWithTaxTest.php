<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogGraphQl;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Verify GraphQL price filter uses displayed (incl. tax) values when catalog prices exclude tax.
 *
 * @magentoApiDataFixture Magento/CatalogGraphQl/_files/products_price_filter_tax.php
 */
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

    public function testPriceFilterIncludesProductWithDisplayedPriceAtLowerBound(): void
    {
        $response = $this->graphQlQuery(
            sprintf(self::QUERY, '10', '15', $this->getDefaultCategoryUid())
        );

        self::assertCount(2, $response['products']['items']);
        self::assertSame('graphql_price_filter_a', $response['products']['items'][0]['sku']);
        self::assertEquals(10, $response['products']['items'][0]['price_range']['minimum_price']['final_price']['value']);
        self::assertSame('graphql_price_filter_b', $response['products']['items'][1]['sku']);
        self::assertEquals(10.75, $response['products']['items'][1]['price_range']['minimum_price']['final_price']['value']);
    }

    public function testPriceFilterExcludesProductBelowDisplayedFromValue(): void
    {
        $response = $this->graphQlQuery(
            sprintf(self::QUERY, '10', '10.74', $this->getDefaultCategoryUid())
        );

        self::assertCount(1, $response['products']['items']);
        self::assertSame('graphql_price_filter_a', $response['products']['items'][0]['sku']);
        self::assertEquals(10, $response['products']['items'][0]['price_range']['minimum_price']['final_price']['value']);
    }

    public function testPriceFilterExcludesProductAboveDisplayedToValue(): void
    {
        $response = $this->graphQlQuery(
            sprintf(self::QUERY, '9', '9.99', $this->getDefaultCategoryUid())
        );

        self::assertCount(1, $response['products']['items']);
        self::assertSame('graphql_price_filter_a', $response['products']['items'][0]['sku']);
    }

    private function getDefaultCategoryUid(): string
    {
        return base64_encode('2');
    }
}
