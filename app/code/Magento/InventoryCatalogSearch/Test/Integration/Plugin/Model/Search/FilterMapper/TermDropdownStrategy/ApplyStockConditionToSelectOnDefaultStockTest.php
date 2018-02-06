<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Test\Integration\Plugin\Model\Search\FilterMapper\TermDropdownStrategy;

use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy\ApplyStockConditionToSelect;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ApplyStockConditionToSelectOnDefaultStockTest extends TestCase
{
    /**
     * @var ApplyStockConditionToSelect
     */
    private $applyStockCondition;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->applyStockCondition = Bootstrap::getObjectManager()->get(ApplyStockConditionToSelect::class);

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
        $this->applyStockCondition->execute('eav_index', 'eav_index_stock', $select);

        $select->where('eav_index_stock.is_salable = 1');

        $result = $select->query()->fetchAll();

        self::assertEquals(2, count($result));
    }
}
