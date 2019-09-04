<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver;
use Magento\ConfigurableProduct\Pricing\Price\FinalPriceResolver;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for LowestPriceOptionProvider.
 */
class LowestPriceOptionProviderTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var string
     */
    private $storeCodeBefore;

    /**
     * @var  ConfigurablePriceResolver
     */
    private $configurablePriceResolver;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->storeCodeBefore = $this->storeManager->getStore()->getCode();
        $finalPrice = $this->objectManager->get(FinalPriceResolver::class);

        $this->configurablePriceResolver = $this->objectManager->create(
            ConfigurablePriceResolver::class,
            ['priceResolver' => $finalPrice]
        );
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @return void
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testGetProductsWithAllChildren()
    {
        $this->storeManager->setCurrentStore('store_for_us_website');

        $configurableProduct = $this->productRepository->get('configurable', false, null, true);
        $lowestPriceChildrenProducts = $this->createLowestPriceOptionsProvider()->getProducts($configurableProduct);
        self::assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        self::assertEquals(10, $lowestPriceChildrenProduct->getPrice());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/set_product_configurable_out_of_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @return void
     *
     * @magentoDbIsolation disabled
     */
    public function testGetProductsIfOneOfChildIsOutOfStock(): void
    {
        $this->storeManager->setCurrentStore('store_for_us_website');

        $configurableProduct = $this->productRepository->get('configurable', false, null, true);
        $lowestPriceChildrenProducts = $this->createLowestPriceOptionsProvider()->getProducts($configurableProduct);
        self::assertCount(1, $lowestPriceChildrenProducts);
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        self::assertEquals(20, $lowestPriceChildrenProduct->getPrice());
    }

    /**
     * Tests getProducts method.
     *
     * Tests getProducts method will find Configurable Product Links in non-default stock when display out of stock
     * config is setted to "Yes" option.
     *
     * @return void
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable_in_us_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDbIsolation disabled
     * @magentoConfigFixture store_for_us_website_store cataloginventory/options/show_out_of_stock 1
     */
    public function testGetProductsWhenOutOfStockInDefaultStock(): void
    {
        $this->storeManager->setCurrentStore('store_for_us_website');
        $configurableProduct = $this->productRepository->get(
            'configurable',
            false,
            null,
            true
        );
        $lowestPriceChildrenProducts = $this->createLowestPriceOptionsProvider()->getProducts($configurableProduct);
        /**
         * As ConfigurableReguralPrice and ConfigurablePriceResolver look for LowestPriceOptionProvider can return
         * array of products "greater than" assertion is used.
         */
        self::assertGreaterThan(0, count($lowestPriceChildrenProducts));
        $lowestPriceChildrenProduct = reset($lowestPriceChildrenProducts);
        self::assertEquals(10, $lowestPriceChildrenProduct->getPrice());
    }

    /**
     * As LowestPriceOptionsProviderInterface used multiple times in scope
     * of one test we need to always recreate it and prevent internal caching in property
     *
     * @return LowestPriceOptionsProviderInterface
     */
    private function createLowestPriceOptionsProvider()
    {
        return $this->objectManager->create(
            LowestPriceOptionsProviderInterface::class
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        if (null !== $this->storeCodeBefore) {
            $this->storeManager->setCurrentStore($this->storeCodeBefore);
        }
    }
}
