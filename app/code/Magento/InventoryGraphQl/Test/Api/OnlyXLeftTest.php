<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGraphQl\Test\Api;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * GraphQl tests for "Only x left" with different stock and website combinations.
 */
class OnlyXLeftTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $preparedValueFactory = $this->objectManager->get(PreparedValueFactory::class);
        $resource = $this->objectManager->get(Data::class);
        $value = $preparedValueFactory->create(
            'cataloginventory/options/stock_threshold_qty',
            101,
            'default',
            0
        );
        $resource->save($value);
        $reinitableConfig = $this->objectManager->create(
            ReinitableConfigInterface::class
        );
        $reinitableConfig->reinit();
    }

    /**
     * Verify "Only x left" default stock, main website.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/909213/scenarios/3056515
     *
     * @return void
     */
    public function testOnlyXLeftDefaultStockMainWebsite(): void
    {
        $productSku = 'simple';
        $query = <<<QUERY
        {
            products(filter: { sku: { like: "{$productSku}"}})
          {
            items {
              only_x_left_in_stock
            }
          }
        }
QUERY;

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertEquals('100', $response['products']['items'][0]['only_x_left_in_stock']);
    }

    /**
     * Verify "Only x left" after order placement on default stock, additional website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/909213/scenarios/3127318
     *
     * @return void
     */
    public function testOnlyXLeftDefaultStockAdditionalWebsite(): void
    {
        $this->assignProductToAdditionalWebsite('simple', 'eu_website');
        $this->assignWebsiteToStock(1, 'eu_website');
        $productSku = 'simple';
        $query = <<<QUERY
        {
            products(filter: { sku: { like: "{$productSku}"}})
          {
            items {
              only_x_left_in_stock
            }
          }
        }
QUERY;

        $headerMap = ['Store' => 'store_for_eu_website'];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertEquals('100', $response['products']['items'][0]['only_x_left_in_stock']);
    }

    /**
     * Verify "Only x left" after order placement on additional stock, main website.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/source_items_for_simple_on_multi_source.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/909213/scenarios/3127130
     *
     * @return void
     */
    public function testOnlyXLeftAdditionalStockMainWebsite(): void
    {
        $this->assignWebsiteToStock(10, 'base');
        $productSku = 'simple';
        $query = <<<QUERY
        {
            products(filter: { sku: { like: "{$productSku}"}})
          {
            items {
              only_x_left_in_stock
            }
          }
        }
QUERY;

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertEquals('14', $response['products']['items'][0]['only_x_left_in_stock']);
    }

    /**
     * Verify "Only x left" after order placement on additional stock, additional website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/source_items_for_simple_on_multi_source.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/909213/scenarios/3127227
     *
     * @return void
     */
    public function testOnlyXLeftAdditionalStockAdditionalWebsite(): void
    {
        $this->assignWebsiteToStock(10, 'eu_website');
        $this->assignProductToAdditionalWebsite('simple', 'eu_website');
        $productSku = 'simple';
        $query = <<<QUERY
        {
            products(filter: { sku: { like: "{$productSku}"}})
          {
            items {
              only_x_left_in_stock
            }
          }
        }
QUERY;

        $headerMap = ['Store' => 'store_for_eu_website'];
        $response = $this->graphQlQuery($query, [], '', $headerMap);

        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertEquals('14', $response['products']['items'][0]['only_x_left_in_stock']);
    }

    /**
     * Assign test products to additional website.
     *
     * @param string $sku
     * @param string $websiteCode
     * @return void
     */
    private function assignProductToAdditionalWebsite(string $sku, string $websiteCode): void
    {
        $websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $websiteId = $websiteRepository->get($websiteCode)->getId();
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($sku);
        $product->setWebsiteIds([$websiteId]);
        $productRepository->save($product);
    }

    /**
     * Assign website to stock as sales channel.
     *
     * @param int $stockId
     * @param string $websiteCode
     * @return void
     */
    private function assignWebsiteToStock(int $stockId, string $websiteCode): void
    {
        $stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $salesChannelFactory = Bootstrap::getObjectManager()->get(SalesChannelInterfaceFactory::class);
        $stock = $stockRepository->get($stockId);
        $extensionAttributes = $stock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels();

        $salesChannel = $salesChannelFactory->create();
        $salesChannel->setCode($websiteCode);
        $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);
        $salesChannels[] = $salesChannel;

        $extensionAttributes->setSalesChannels($salesChannels);
        $stockRepository->save($stock);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $resource = $this->objectManager->get(Data::class);
        $resource->clearScopeData('default', 0);
    }
}
