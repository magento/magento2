<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Operation;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Update Inventory Stock Item Data from CatalogInventory to Inventory Source Item with default source ID
 */
class UpdateInventorySourceItem
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * Insert Stock Item to Inventory Source Item by raw MySQL query
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $defaultSourceId = $this->defaultSourceProvider->getId();
        $sourceItemTable = $setup->getTable(SourceItem::TABLE_NAME_SOURCE_ITEM);
        $stockItemTable = $setup->getTable('cataloginventory_stock_item');
        $productTable = $setup->getTable('catalog_product_entity');

        $selectForInsert = $this->resourceConnection->getConnection()->select()->from(
            $stockItemTable,
            ['source_id' => new \Zend_Db_Expr($defaultSourceId), 'qty', 'is_in_stock']
        )->join($productTable, 'entity_id = product_id', 'sku')->where('website_id = ?', 0);

        $sql = $this->resourceConnection->getConnection()->insertFromSelect(
            $selectForInsert,
            $sourceItemTable,
            ['source_id', 'quantity', 'status', 'sku']
        );

        $this->resourceConnection->getConnection()->query($sql);
    }
}
