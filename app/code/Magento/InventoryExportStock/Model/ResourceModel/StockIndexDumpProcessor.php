<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model\ResourceModel;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryExportStock\Model\GetQtyForNotManageStock;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition\ManageStockCondition;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetStockIndexDump constructor
     *
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param ResourceConnection $resourceConnection
     * @param ManageStockCondition $manageStockCondition
     * @param GetQtyForNotManageStock $getQtyForNotManageStock
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        ResourceConnection $resourceConnection,
        ManageStockCondition $manageStockCondition,
        GetQtyForNotManageStock $getQtyForNotManageStock,
        LoggerInterface $logger
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->resourceConnection = $resourceConnection;
        $this->manageStockCondition = $manageStockCondition;
        $this->getQtyForNotManageStock = $getQtyForNotManageStock;
        $this->logger = $logger;
    }

    /**
     * Provides sku and qty of products dumping them from stock index table
     *
     * @param int $stockId
     * @return array
     * @throws LocalizedException
     */
    public function execute(int $stockId): array
    {
        $this->connection = $this->resourceConnection->getConnection();
        $select = $this->connection->select();
        try {
            $select->union([
                $this->getStockItemSelect($stockId),
                $this->getStockIndexSelect($stockId)
            ]);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new LocalizedException(__('Something went wrong. Export couldn\'t be executed, See log files for error details'));
        }

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
                    'is_salable' => 'is_salable',
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
            [new Zend_Db_Expr($this->getQtyForNotManageStock->execute() . ' as qty'),
            new Zend_Db_Expr('1 as is_salable')]
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
