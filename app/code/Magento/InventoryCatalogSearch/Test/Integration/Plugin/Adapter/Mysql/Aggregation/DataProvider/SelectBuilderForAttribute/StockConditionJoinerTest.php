<?php
/**
 * Copyright :copyright: Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute\StockConditionJoiner;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class StockConditionJoinerTest extends TestCase
{
    /**
     * @var StockConditionJoiner
     */
    private $stockConditionJoiner;

    /**
     * @var ResourceConnection
     */
    private $resource;

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
        $this->resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $this->stockConditionJoiner = Bootstrap::getObjectManager()->get(StockConditionJoiner::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->storeCodeBefore = $this->storeManager->getStore()->getCode();

        parent::setUp();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_link.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @param string $store
     * @param int $expectedSize
     *
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(string $store, int $expectedSize)
    {
        $this->storeManager->setCurrentStore($store);

        $connection = $this->resource->getConnection();
        $select = $connection->select();
        $select->from(
            ['main_table' => $this->resource->getTableName('catalog_product_index_eav')],
            ['main_table.entity_id', 'main_table.value']
        )->distinct();

        $this->stockConditionJoiner->execute($select);

        self::assertEquals($expectedSize, count($select->query()->fetchAll()));
    }

    /**
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            ['store_for_eu_website', 1],
            ['store_for_us_website', 1],
            ['store_for_global_website', 2],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        if (null !== $this->storeCodeBefore) {
            $this->storeManager->setCurrentStore($this->storeCodeBefore);
        }

        parent::tearDown();
    }
}
