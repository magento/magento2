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
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;

class ApplyStockConditionToSelectOnDefaultStockTest extends TestCase
{
    /**
     * @var ApplyStockConditionToSelect
     */
    private $applyStockConditionToSelect;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->applyStockConditionToSelect = Bootstrap::getObjectManager()->get(ApplyStockConditionToSelect::class);
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->getSourceItemsBySku = Bootstrap::getObjectManager()->get(GetSourceItemsBySkuInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     *
     * @magentoDbIsolation disabled
     */
    public function testExecute()
    {
        /** @var ResourceConnection $resource */
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);

        /** @var Select $select */
        $select = $resource->getConnection()->select();
        $select->from(['eav_index' => $resource->getTableName('catalog_product_index_eav')], 'entity_id');
        $this->applyStockConditionToSelect->execute('eav_index', 'eav_index_stock', $select);

        $result = $select->query()->fetchAll();

        /**
         * Taking into account that `inventory_stock_1` is a MySQL View referring cataloginventory_stock_status table
         * all the legacy StockItems will get into the Default Index
         */
        self::assertEquals(5, count($result));

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, ['SKU-1', 'SKU-2', 'SKU-3', 'SKU-4', 'SKU-5'], 'in')
            ->addFilter(SourceItemInterface::SOURCE_CODE, $this->defaultSourceProvider->getCode())
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        self::assertEquals(4, count($sourceItems));

        /**
         * For SKU-5 we should not have Source Item created
         */
        self::assertEmpty($this->getSourceItemsBySku->execute('SKU-5'));
    }
}
