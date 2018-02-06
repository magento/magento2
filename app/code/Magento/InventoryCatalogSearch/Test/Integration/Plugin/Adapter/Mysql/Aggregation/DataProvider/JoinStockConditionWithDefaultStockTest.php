<?php
/**
 * Copyright :copyright: Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Test\Integration\Plugin\Adapter\Mysql\Aggregation\DataProvider;

use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute\JoinStockCondition;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class JoinStockConditionWithDefaultStockTest extends TestCase
{
    /**
     * @var JoinStockCondition
     */
    private $joinStockCondition;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $this->joinStockCondition = Bootstrap::getObjectManager()->get(JoinStockCondition::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     */
    public function testExecute()
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();
        $select->from(
            ['main_table' => $this->resource->getTableName('catalog_product_index_eav')],
            ['main_table.entity_id', 'main_table.value']
        )->distinct();

        $this->joinStockCondition->execute($select);
        self::assertEquals(2, count($select->query()->fetchAll()));
    }
}
