<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Checks if stock status correctly displays for configurable variants.
 */
class ConfigurableProductStockStatusTest extends GraphQlAbstract
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->stockRegistry = Bootstrap::getObjectManager()->create(StockRegistryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Framework/Search/_files/product_configurable.php
     * @magentoConfigFixture default_store cataloginventory/options/show_out_of_stock 1
     */
    public function testConfigurableProductShowOutOfStock()
    {
        $parentSku = 'configurable';
        $childSkuOutOfStock = 'simple_1010';
        $stockItem = $this->stockRegistry->getStockItemBySku($childSkuOutOfStock);
        $stockItem->setQty(0);
        $this->stockRegistry->updateStockItemBySku($childSkuOutOfStock, $stockItem);
        $query = $this->getQuery($parentSku);
        $response = $this->graphQlQuery($query);
        $this->assertArraySubset(
            [['product' => ['sku' => $childSkuOutOfStock, 'stock_status' => 'OUT_OF_STOCK']]],
            $response['products']['items'][0]['variants']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Framework/Search/_files/product_configurable.php
     * @magentoConfigFixture default_store cataloginventory/options/show_out_of_stock 0
     */
    public function testConfigurableProductDoNotShowOutOfStock()
    {
        $parentSku = 'configurable';
        $childSkuOutOfStock = 'simple_1010';
        $stockItem = $this->stockRegistry->getStockItemBySku($childSkuOutOfStock);
        $stockItem->setQty(0);
        $this->stockRegistry->updateStockItemBySku($childSkuOutOfStock, $stockItem);
        $query = $this->getQuery($parentSku);
        $response = $this->graphQlQuery($query);
        $this->assertEquals(
            [['product' => ['sku' => 'simple_1020', 'stock_status' => 'IN_STOCK']]],
            $response['products']['items'][0]['variants']
        );
    }

    /**
     * @param string $sku
     * @return string
     */
    private function getQuery(string $sku)
    {
        return <<<QUERY
        {
            products(filter: {sku: {eq: "{$sku}"}})
            {
                items {
                    sku
                    ... on ConfigurableProduct {
                        variants {
                            product {
                                sku
                                stock_status
                            }
                        }
                    }
                }
            }
        }
QUERY;
    }
}
