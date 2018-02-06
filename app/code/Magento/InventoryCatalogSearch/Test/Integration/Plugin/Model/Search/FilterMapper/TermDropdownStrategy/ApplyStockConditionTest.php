<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Test\Integration\Plugin\Model\Search\FilterMapper\TermDropdownStrategy;

use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy\ApplyStockCondition;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ApplyStockConditionTest extends TestCase
{
    /**
     * @var ApplyStockCondition
     */
    private $applyStockCondition;

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
        $this->applyStockCondition = Bootstrap::getObjectManager()->get(ApplyStockCondition::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->storeCodeBefore = $this->storeManager->getStore()->getCode();

        parent::setUp();
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
     */
    public function testExecute($store, $expectedSize)
    {
        $this->storeManager->setCurrentStore($store);

        /** @var ResourceConnection $resource */
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);

        /** @var Select $select */
        $select = $resource->getConnection()->select();
        $select->from(['eav_index' => $resource->getTableName('catalog_product_index_eav')], 'entity_id');
        $this->applyStockCondition->execute('eav_index', 'eav_index_stock', $select);
        $select->where('eav_index_stock.is_salable = 1');

        $result = $select->query()->fetchAll();

        self::assertEquals($expectedSize, count($result));
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
        parent::tearDown();

        if (null !== $this->storeCodeBefore) {
            $this->storeManager->setCurrentStore($this->storeCodeBefore);
        }
    }
}
