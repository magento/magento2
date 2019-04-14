<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\InventoryExportStock\Model\GetQtyForNotManageStock;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition\ManageStockCondition;
use Zend_Db_Expr;
use Zend_Db_Select_Exception;

/**
 * Class GetStockIndexDump provides sku and qty of products dumping them from stock index table
 */
class StockIndexDumpProcessor
{
    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ManageStockCondition
     */
    private $manageStockCondition;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var GetQtyForNotManageStock
     */
    private $getQtyForNotManageStock;

    /**
     * GetStockIndexDump constructor
     *
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param ResourceConnection $resourceConnection
     * @param ManageStockCondition $manageStockCondition
     * @param GetQtyForNotManageStock $getQtyForNotManageStock
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        ResourceConnection $resourceConnection,
        ManageStockCondition $manageStockCondition,
        GetQtyForNotManageStock $getQtyForNotManageStock
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->resourceConnection = $resourceConnection;
        $this->manageStockCondition = $manageStockCondition;
        $this->getQtyForNotManageStock = $getQtyForNotManageStock;
    }

    /**
     * Provides sku and qty of products dumping them from stock index table
     *
     * @param int $stockId
     * @return array
     * @throws Zend_Db_Select_Exception
     */
    public function execute(int $stockId): array
    {
        $this->connection = $this->resourceConnection->getConnection();
        $select = $this->connection->select();
        $select->union([
            $this->getStockItemSelect($stockId),
            $this->getStockIndexSelect($stockId)
        ]);

        return $this->connection->fetchAll($select);
    }

    /**
     * Provides stock select
     *
     * @param int $stockId
     * @return Select
     */
    private function getStockIndexSelect(int $stockId): Select
    {
        $stockIndexTableName = $this->resourceConnection
            ->getTableName($this->stockIndexTableNameResolver->execute($stockId));

        return $this->connection->select()
            ->from(
                $stockIndexTableName,
                [
                    'qty' => 'quantity',
                    'sku' => 'sku'
                ]
            );
    }

    /**
     * Provides stock item select
     *
     * @param int $stockId
     * @return Select
     */
    private function getStockItemSelect(int $stockId): Select
    {
        $legacyStockItemTable = $this->resourceConnection
            ->getTableName('cataloginventory_stock_item');
        $productEntityTable = $this->resourceConnection
            ->getTableName('catalog_product_entity');
        $select = $this->connection->select();

        $select->from(
            ['legacy_stock_item' => $legacyStockItemTable],
            new Zend_Db_Expr('"' . $this->getQtyForNotManageStock->execute() . '" as qty')
        )->join(
            ['product_entity' => $productEntityTable],
            'legacy_stock_item.product_id = product_entity.entity_id',
            ['sku']
        )->where(
            $this->manageStockCondition->execute($select)
        )->where(
            'legacy_stock_item.stock_id = ?',
            $stockId
        );

        return $select;
    }
}
