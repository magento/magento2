<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogInventory;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\CatalogInventory\Model\Configuration;

/**
 * Test for the product only x left in stock
 */
class ProductOnlyXLeftInStockTest extends GraphQlAbstract
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var Config $config
     */
    private $resourceConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = ObjectManager::getInstance();
        $this->productRepository = $objectManager->create(ProductRepositoryInterface::class);
        $this->resourceConfig = $objectManager->get(Config::class);
        $this->scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $this->reinitConfig = $objectManager->get(ReinitableConfigInterface::class);
    }

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

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_out_of_stock_without_categories.php
     * @magentoConfigFixture default_store cataloginventory/options/stock_threshold_qty 120
     */
    public function testQueryProductOnlyXLeftInStockOutstock()
    {
        $productSku = 'simple';
        $showOutOfStock = $this->scopeConfig->getValue(Configuration::XML_PATH_SHOW_OUT_OF_STOCK);

        $this->resourceConfig->saveConfig(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, 1);
        $this->reinitConfig->reinit();

        // need to resave product to reindex it with new configuration.
        $product = $this->productRepository->get($productSku);
        $this->productRepository->save($product);
        
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

        $this->resourceConfig->saveConfig(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, $showOutOfStock);
        $this->reinitConfig->reinit();

        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('only_x_left_in_stock', $response['products']['items'][0]);
        $this->assertEquals(0, $response['products']['items'][0]['only_x_left_in_stock']);
    }
}
