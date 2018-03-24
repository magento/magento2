<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\Price;

use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventorySales\Model\GetStockItemDataInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ConfigurableProductsIndexerTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->getSourceItemsBySku = Bootstrap::getObjectManager()->get(GetSourceItemsBySkuInterface::class);
        $this->sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemDataInterface::class);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @return void
     */
    // @codingStandardsIgnoreEnd
    public function testOneSimpleChangesToOutOfStock()
    {
        $this->changeStockStatusForSku('simple_11', SourceItemInterface::STATUS_OUT_OF_STOCK);

        $data = $this->getStockItemData->execute('configurable_1', 20);
        $this->assertEquals(1, $data['is_salable']);

        $data = $this->getStockItemData->execute('configurable_2', 20);
        $this->assertEquals(1, $data['is_salable']);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @return void
     */
    // @codingStandardsIgnoreEnd
    public function testAllSimplesChangesToOutOfStock()
    {
        $this->changeStockStatusForSku('simple_11', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_21', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_31', SourceItemInterface::STATUS_OUT_OF_STOCK);

        $data = $this->getStockItemData->execute('configurable_1', 20);
        $this->assertEquals(0, $data['is_salable']);

        $data = $this->getStockItemData->execute('configurable_2', 20);
        $this->assertEquals(1, $data['is_salable']);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/set_simples_out_of_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @return void
     */
    // @codingStandardsIgnoreEnd
    public function testOneSimpleChangesToInStock()
    {
        $this->changeStockStatusForSku('simple_21', SourceItemInterface::STATUS_IN_STOCK);

        $data = $this->getStockItemData->execute('configurable_1', 20);
        $this->assertEquals(1, $data['is_salable']);

        $data = $this->getStockItemData->execute('configurable_2', 20);
        $this->assertEquals(1, $data['is_salable']);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/set_simples_out_of_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @return void
     */
    // @codingStandardsIgnoreEnd
    public function testAllSimplesChangesToInStock()
    {
        $this->changeStockStatusForSku('simple_11', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_21', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_31', SourceItemInterface::STATUS_IN_STOCK);

        $data = $this->getStockItemData->execute('configurable_1', 20);
        $this->assertEquals(1, $data['is_salable']);

        $data = $this->getStockItemData->execute('configurable_2', 20);
        $this->assertEquals(1, $data['is_salable']);
    }

    /**
     * @param string $sku
     * @param int $stockStatus
     */
    private function changeStockStatusForSku(string $sku, int $stockStatus)
    {
        $sourceItems = $this->getSourceItemsBySku->execute($sku);
        $changesSourceItems = [];
        foreach ($sourceItems->getItems() as $sourceItem) {
            $sourceItem->setStatus($stockStatus);
            $changesSourceItems[] = $sourceItem;
        }
        $this->sourceItemsSave->execute($changesSourceItems);
    }
}
