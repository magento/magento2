<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Operation;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

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
     * @var string
     */
    private $tableNameSourceItem;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param string $tableNameSourceItem
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DefaultSourceProviderInterface $defaultSourceProvider,
        $tableNameSourceItem
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->tableNameSourceItem = $tableNameSourceItem;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $defaultSourceCode = $this->defaultSourceProvider->getCode();
        $sourceItemTable = $setup->getTable($this->tableNameSourceItem);
        $legacyStockItemTable = $setup->getTable('cataloginventory_stock_item');
        $productTable = $setup->getTable('catalog_product_entity');

        $selectForInsert = $this->resourceConnection->getConnection()
            ->select()
            ->from(
                ['legacy_stock_item' => $legacyStockItemTable],
                [
                    'source_code' => new \Zend_Db_Expr('\'' . $defaultSourceCode . '\''),
                    'qty',
                    'is_in_stock'
                ]
            )
            ->join(
                ['product' => $productTable],
                'product.entity_id = legacy_stock_item.product_id',
                'sku'
            )
            ->where('website_id = ?', 0);

        $sql = $this->resourceConnection->getConnection()->insertFromSelect(
            $selectForInsert,
            $sourceItemTable,
            [
                SourceItemInterface::SOURCE_CODE,
                SourceItemInterface::QUANTITY,
                SourceItemInterface::STATUS,
                SourceItemInterface::SKU,
            ],
            Mysql::INSERT_ON_DUPLICATE
        );
        $this->resourceConnection->getConnection()->query($sql);
    }
}
