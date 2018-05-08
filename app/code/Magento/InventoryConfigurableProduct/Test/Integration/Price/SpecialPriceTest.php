<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver;
use Magento\ConfigurableProduct\Pricing\Price\FinalPriceResolver;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SpecialPriceTest extends TestCase
{
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

        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->storeCodeBefore = $this->storeManager->getStore()->getCode();
        $finalPrice = Bootstrap::getObjectManager()->get(FinalPriceResolver::class);

        $this->configurablePriceResolver = Bootstrap::getObjectManager()->create(
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
    public function testResolvePrice()
    {
        $this->storeManager->setCurrentStore('store_for_us_website');

        $specialPrice = 2;

        /** @var Product $childProduct */
        $childProduct = $this->productRepository->get('simple_10', true);
        $childProduct->setData('special_price', $specialPrice);
        $this->productRepository->save($childProduct);

        $configurableProduct = $this->productRepository->get('configurable', false, null, true);
        $actualPrice = $this->configurablePriceResolver->resolvePrice($configurableProduct);

        self::assertEquals($specialPrice, $actualPrice);
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
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/set_product_configurable_out_of_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @return void
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testResolvePriceIfChildWithSpecialPriceOutOfStock()
    {
        $this->storeManager->setCurrentStore('store_for_us_website');

        /** @var Product $childProduct */
        $childProduct = $this->productRepository->get('simple_10', true);
        $childProduct->setData('special_price', 2);
        $this->productRepository->save($childProduct);

        $configurableProduct = $this->productRepository->get('configurable', false, null, true);
        $actualPrice = $this->configurablePriceResolver->resolvePrice($configurableProduct);

        self::assertEquals(20, $actualPrice);
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
