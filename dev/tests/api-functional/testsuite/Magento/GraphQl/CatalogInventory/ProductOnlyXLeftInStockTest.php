<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogInventory;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for the product only x left in stock
 */
class ProductOnlyXLeftInStockTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_all_fields.php
     */
    public function testQueryProductOnlyXLeftInStockDisabled()
    {
        $productSku = 'simple';

        $query = <<<QUERY
        {
            products(filter: {sku: {eq: "{$productSku}"}})
            {
                items {
                    only_x_left_in_stock
                }
            }
        }
QUERY;

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('only_x_left_in_stock', $response['products']['items'][0]);
        $this->assertNull($response['products']['items'][0]['only_x_left_in_stock']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_all_fields.php
     * @magentoConfigFixture default_store cataloginventory/options/stock_threshold_qty 120
     */
    public function testQueryProductOnlyXLeftInStockEnabled()
    {
        $productSku = 'simple';

        $query = <<<QUERY
        {
            products(filter: {sku: {eq: "{$productSku}"}})
            {
                items {
                    only_x_left_in_stock            
                }
            }
        }
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('only_x_left_in_stock', $response['products']['items'][0]);
        $this->assertEquals(100, $response['products']['items'][0]['only_x_left_in_stock']);
    }
}
