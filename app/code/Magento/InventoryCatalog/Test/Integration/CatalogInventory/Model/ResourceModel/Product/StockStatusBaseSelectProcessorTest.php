<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\CatalogInventory\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class StockStatusBaseSelectProcessorTest extends TestCase
{
    /**
     * @var BaseSelectProcessorInterface
     */
    private $productResourceModel;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

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

        $this->productResourceModel = Bootstrap::getObjectManager()->get(BaseSelectProcessorInterface::class);
        $this->resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->storeCodeBefore = $this->storeManager->getStore()->getCode();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/product_configurable_out_of_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @return void
     */
    public function testProcess()
    {
        $this->storeManager->setCurrentStore('store_for_us_website');

        $select = $this->resourceConnection->getConnection()->select();
        $productTable = $this->resourceConnection->getTableName('catalog_product_entity');
        $select
            ->from(['parent' => $productTable])
            ->joinInner(
                ['link' => $this->resourceConnection->getTableName('catalog_product_relation')],
                'link.parent_id = parent.entity_id',
                []
            )
            ->joinInner(
                ['child' => $productTable],
                'child.entity_id = link.child_id',
                []
            );

        $this->productResourceModel->process($select);

        self::assertEquals(2, count($select->query()->fetchAll()));
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
