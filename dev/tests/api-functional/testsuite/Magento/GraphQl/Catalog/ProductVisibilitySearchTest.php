<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductVisibilitySearchTest extends GraphQlAbstract
{
    /**
     * Verify that a product with "Catalog Only" visibility
     * is not returned in GraphQL product search results.
     */
    #[DataFixture(
        ProductFixture::class,
        ['price' => 1, 'name' => 'API_TEST', 'visibility' => Visibility::VISIBILITY_IN_CATALOG],
        'prod1'
    )]
    public function testProductNotReturnedWhenVisibilityIsCatalogOnly(): void
    {
        $query = <<<'QUERY'
            query {
              products(
                search: "API"
                filter: { price: { from: "1" } }
              ) {
                items {
                  name
                  sku
                }
              }
            }
            QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertEmpty(
            $response['products']['items'],
            'Expected no products to be returned when product is set to Catalog-only visibility.'
        );
    }
}
