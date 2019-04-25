<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Test\Integration\Model\Search\FilterMapper\TermDropdownStrategy;

use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy\ApplyStockConditionToSelect;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ApplyStockConditionToSelectTest extends TestCase
{
    /**
     * @var ApplyStockConditionToSelect
     */
    private $applyStockConditionToSelect;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $storeCodeBefore;

    /**
     * @var ResourceConnection $resource
     */
    private $resource;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->applyStockConditionToSelect = Bootstrap::getObjectManager()->get(ApplyStockConditionToSelect::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->storeCodeBefore = $this->storeManager->getStore()->getCode();
        $this->resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
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
     * @dataProvider executeDataProvider
     *
     * @magentoDbIsolation disabled
     */
    public function testExecute($store, $expectedSize)
    {
        $this->storeManager->setCurrentStore($store);

        /** @var Select $select */
        $select = $this->resource->getConnection()->select();
        $select->from(['eav_index' => $this->resource->getTableName('catalog_product_index_eav')], 'entity_id');
        $this->applyStockConditionToSelect->execute('eav_index', 'eav_index_stock', $select);

        $result = $select->query()->fetchAll();

        self::assertEquals($expectedSize, count($result));
    }

    /**
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            ['store_for_eu_website', 4],
            ['store_for_us_website', 1],
            ['store_for_global_website', 5],
        ];
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
