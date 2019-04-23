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
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryExportStock\Model\GetQtyForNotManageStock;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition\ManageStockCondition as NotManageStockCondition;
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
     * @var NotManageStockCondition
     */
    private $notManageStockCondition;

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
     * @var ManageStockCondition
     */
    private $manageStockCondition;

    /**
     * GetStockIndexDump constructor
     *
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param ResourceConnection $resourceConnection
     * @param NotManageStockCondition $notManageStockCondition
     * @param ManageStockCondition $manageStockCondition
     * @param GetQtyForNotManageStock $getQtyForNotManageStock
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        ResourceConnection $resourceConnection,
        NotManageStockCondition $notManageStockCondition,
        ManageStockCondition $manageStockCondition,
        GetQtyForNotManageStock $getQtyForNotManageStock,
        LoggerInterface $logger
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->resourceConnection = $resourceConnection;
        $this->notManageStockCondition = $notManageStockCondition;
        $this->manageStockCondition = $manageStockCondition;
        $this->getQtyForNotManageStock = $getQtyForNotManageStock;
        $this->logger = $logger;
    }

    /**
     * Provides sku and qty of products dumping them from stock index table
     *
     * @param int $websiteId
     * @param int $stockId
     * @return array
     * @throws LocalizedException
     */
    public function execute(int $websiteId, int $stockId): array
    {
        $this->connection = $this->resourceConnection->getConnection();
        $select = $this->connection->select();
        try {
            $select->union([
                $this->getStockItemSelect($websiteId),
                $this->getStockIndexSelect($websiteId, $stockId)
            ]);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new LocalizedException(__('Something went wrong. Export couldn\'t be executed, See log files for error details'));
        }

        return $this->connection->fetchAll($select);
    }

    /**
     * Provides stock select
     *
     * @param int $websiteId
     * @param int $stockId
     * @return Select
     */
    private function getStockIndexSelect(int $websiteId, int $stockId): Select
    {
        $stockIndexTableName = $this->resourceConnection
            ->getTableName($this->stockIndexTableNameResolver->execute($stockId));

        $legacyStockItemTable = $this->resourceConnection
            ->getTableName('cataloginventory_stock_item');
        $productEntityTable = $this->resourceConnection
            ->getTableName('catalog_product_entity');
        $productWebsiteTable = $this->resourceConnection
            ->getTableName('catalog_product_website');

        $select = $this->connection->select();
        $select->from(
            ['stock_index' => $stockIndexTableName],
            [
                'qty' => 'quantity',
                'is_salable' => 'is_salable',
                'sku' => 'sku'
            ]
        )->join(
            ['product_entity' => $productEntityTable],
            'product_entity.sku=stock_index.sku',
            ''
        )->join(
            ['legacy_stock_item' => $legacyStockItemTable],
            'legacy_stock_item.product_id = product_entity.entity_id',
            ''
        )->join(
            ['prod_website' => $productWebsiteTable],
            'legacy_stock_item.product_id = prod_website.product_id',
            ''
        )->where(
            $this->manageStockCondition->execute($select)
        )->where(
            'prod_website.website_id = ?',
            $websiteId
        );

        return $select;
    }

    /**
     * Provides stock item select
     *
     * @param int $websiteId
     * @return Select
     */
    private function getStockItemSelect(int $websiteId): Select
    {
        $legacyStockItemTable = $this->resourceConnection
            ->getTableName('cataloginventory_stock_item');
        $productEntityTable = $this->resourceConnection
            ->getTableName('catalog_product_entity');
        $select = $this->connection->select();
        $getQtyForNotManageStock = $this->getQtyForNotManageStock->execute();
        if ($getQtyForNotManageStock === null) {
            $getQtyForNotManageStock = 'NULL';
        }
        $select->from(
            ['legacy_stock_item' => $legacyStockItemTable],
            [new Zend_Db_Expr($getQtyForNotManageStock . ' as qty'),
                new Zend_Db_Expr('"1" as is_salable')]
        )->join(
            ['product_entity' => $productEntityTable],
            'legacy_stock_item.product_id = product_entity.entity_id',
            ['sku']
        )->join(
            ['pr_web' => 'catalog_product_website'],
            'legacy_stock_item.product_id = pr_web.product_id',
            ''
        )->where(
            $this->notManageStockCondition->execute($select)
        )->where(
            'pr_web.website_id = ?',
            $websiteId
        );

        return $select;
    }
}
