<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogInventory;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductStockStatusTest extends GraphQlAbstract
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    protected function setUp()
    {
        $this->stockRegistry = Bootstrap::getObjectManager()->create(StockRegistryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_all_fields.php
     */
    public function testQueryProductStockStatusInStock()
    {
        $productSku = 'simple';

        $query = <<<QUERY
        {
            products(filter: {sku: {eq: "{$productSku}"}})
            {
                items {
                    stock_status            
                }
            }
        }
QUERY;

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('stock_status', $response['products']['items'][0]);
        $this->assertEquals('IN_STOCK', $response['products']['items'][0]['stock_status']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_all_fields.php
     * @magentoConfigFixture default_store cataloginventory/options/show_out_of_stock 1
     */
    public function testQueryProductStockStatusOutOfStock()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/167');
        $productSku = 'simple';

        $query = <<<QUERY
        {
            products(filter: {sku: {eq: "{$productSku}"}})
            {
                items {
                    stock_status            
                }
            }
        }
QUERY;

        $stockItem = $this->stockRegistry->getStockItemBySku($productSku);
        $stockItem->setQty(0);
        $this->stockRegistry->updateStockItemBySku($productSku, $stockItem);

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('stock_status', $response['products']['items'][0]);
        $this->assertEquals('OUT_OF_STOCK', $response['products']['items'][0]['stock_status']);
    }
}
