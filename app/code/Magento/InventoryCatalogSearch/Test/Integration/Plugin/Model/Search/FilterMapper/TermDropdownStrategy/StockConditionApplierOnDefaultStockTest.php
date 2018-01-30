<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Test\Integration\Plugin\Model\Search\FilterMapper\TermDropdownStrategy;

use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy\StockConditionApplier;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class StockConditionApplierOnDefaultStockTest extends TestCase
{
    /**
     * @var StockConditionApplier
     */
    private $stockConditionApplier;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->stockConditionApplier = Bootstrap::getObjectManager()->get(StockConditionApplier::class);

        parent::setUp();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     */
    public function testExecute()
    {
        /** @var ResourceConnection $resource */
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);

        /** @var Select $select */
        $select = $resource->getConnection()->select();
        $select->from(['eav_index' => $resource->getTableName('catalog_product_index_eav')], 'entity_id');
        $this->stockConditionApplier->execute('eav_index', 'eav_index_stock', $select);
        //todo change quantity condition to is_salable after https://github.com/magento-engcom/msi/pull/442
        $select->where('eav_index_stock.quantity > 0');

        $result = $select->query()->fetchAll();

        self::assertEquals(2, count($result));
    }
}
