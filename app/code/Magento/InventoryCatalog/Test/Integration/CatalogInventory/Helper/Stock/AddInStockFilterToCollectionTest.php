<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\CatalogInventory\Helper\Stock;

use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AddInStockFilterToCollectionTest extends TestCase
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
     *
     * @param string $store
     * @param int $expectedSize
     * @return void
     *
     * @dataProvider addInStockFilterToCollectionDataProvider
     *
     * @magentoDbIsolation disabled
     */
    public function testAddInStockFilterToCollection(string $store, int $expectedSize)
    {
        $this->storeManager->setCurrentStore($store);

        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $this->stockHelper->addInStockFilterToCollection($collection);

        self::assertEquals($expectedSize, $collection->getSize());
    }

    /**
     * @return array
     */
    public function addInStockFilterToCollectionDataProvider(): array
    {
        return [
            ['store_for_eu_website', 3],
            ['store_for_us_website', 1],
            ['store_for_global_website', 4],
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
