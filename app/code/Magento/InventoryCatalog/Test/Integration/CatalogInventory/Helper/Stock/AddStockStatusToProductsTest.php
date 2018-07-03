<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\CatalogInventory\Helper\Stock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AddStockStatusToProductsTest extends TestCase
{
    /**
     * @var Stock
     */
    private $stockHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $storeCodeBefore;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->stockHelper = Bootstrap::getObjectManager()->get(Stock::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->storeCodeBefore = $this->storeManager->getStore()->getCode();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @dataProvider addStockStatusToProductsDataProvider
     * @param string $storeCode
     * @param array $productsData
     *
     * @magentoDbIsolation disabled
     */
    public function testAddStockStatusToProducts(string $storeCode, array $productsData)
    {
        $this->storeManager->setCurrentStore($storeCode);

        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $collection->addFieldToFilter(ProductInterface::SKU, ['in' => array_keys($productsData)]);
        $collection->load();
        self::assertCount(3, $collection->getItems());

        $this->stockHelper->addStockStatusToProducts($collection);

        /** @var ProductInterface $product */
        foreach ($collection as $product) {
            self::assertEquals($productsData[$product->getSku()], $product->isSalable());
        }
    }

    /**
     * @return array
     */
    public function addStockStatusToProductsDataProvider(): array
    {
        return [
            'eu_website' => [
                'store_for_eu_website',
                [
                    'SKU-1' => 1,
                    'SKU-2' => 0,
                    'SKU-3' => 0,
                ],
            ],
            'us_website' => [
                'store_for_us_website',
                [
                    'SKU-1' => 0,
                    'SKU-2' => 1,
                    'SKU-3' => 0,
                ],
            ],
            'global_website' => [
                'store_for_global_website',
                [
                    'SKU-1' => 1,
                    'SKU-2' => 1,
                    'SKU-3' => 0,
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->storeManager->setCurrentStore($this->storeCodeBefore);

        parent::tearDown();
    }
}
