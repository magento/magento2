<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGraphQl\Test\Api;

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
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryGraphQl/Test/_files/several_source_items.php
     */
    public function testQueryProductStockStatusInStockWithSources()
    {
        $productSku = 'SKU-1';
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

}
