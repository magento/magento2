<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\CatalogInventory\Model\Indexer\Stock\Action\Full;
use Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\Table\StrategyInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Bundle Stock Status Indexer Resource Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Stock extends DefaultStock
{
    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var StockStatusSelectBuilder
     */
    private $stockStatusSelectBuilder;

    /**
     * @var BundleOptionStockDataSelectBuilder
     */
    private $bundleOptionStockDataSelectBuilder;

    /**
     * @param Context $context
     * @param StrategyInterface $tableStrategy
     * @param Config $eavConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param ActiveTableSwitcher $activeTableSwitcher
     * @param StockStatusSelectBuilder $stockStatusSelectBuilder
     * @param BundleOptionStockDataSelectBuilder $bundleOptionStockDataSelectBuilder
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        StrategyInterface $tableStrategy,
        Config $eavConfig,
        ScopeConfigInterface $scopeConfig,
        ActiveTableSwitcher $activeTableSwitcher,
        StockStatusSelectBuilder $stockStatusSelectBuilder,
        BundleOptionStockDataSelectBuilder $bundleOptionStockDataSelectBuilder,
        $connectionName = null
    ) {
        parent::__construct($context, $tableStrategy, $eavConfig, $scopeConfig, $connectionName);

        $this->activeTableSwitcher = $activeTableSwitcher;
        $this->stockStatusSelectBuilder = $stockStatusSelectBuilder;
        $this->bundleOptionStockDataSelectBuilder = $bundleOptionStockDataSelectBuilder;
    }

    /**
     * Retrieve table name for temporary bundle option stock index
     *
     * @return string
     */
    protected function _getBundleOptionTable()
    {
        return $this->getTable('catalog_product_bundle_stock_index');
    }

    /**
     * Prepare stock status per Bundle options, website and stock
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     *
     * @return $this
     */
    protected function _prepareBundleOptionStockData($entityIds = null, $usePrimaryTable = false)
    {
        $this->_cleanBundleOptionStockData();
        $connection = $this->getConnection();
        $table = $this->getActionType() === Full::ACTION_TYPE
            ? $this->activeTableSwitcher->getAdditionalTableName($this->getMainTable())
            : $this->getMainTable();
        $idxTable = $usePrimaryTable ? $table : $this->getIdxTable();
        $select = $this->bundleOptionStockDataSelectBuilder->buildSelect($idxTable);

        $status = $this->getOptionsStatusExpression();
        $select->columns(['status' => $status]);

        if ($entityIds !== null) {
            $select->where('product.entity_id IN(?)', $entityIds);
        }

        // clone select for bundle product without required bundle options
        $selectNonRequired = clone $select;

        $select->where('bo.required = ?', 1);
        $selectNonRequired->where('bo.required = ?', 0)->having($status . ' = 1');
        $query = $select->insertFromSelect($this->_getBundleOptionTable());
        $connection->query($query);

        $query = $selectNonRequired->insertFromSelect($this->_getBundleOptionTable());
        $connection->query($query);

        return $this;
    }

    /**
     * Get the select object for get stock status by product ids
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $this->_prepareBundleOptionStockData($entityIds, $usePrimaryTable);
        $connection = $this->getConnection();

        $select = parent::_getStockStatusSelect($entityIds, $usePrimaryTable);
        $select = $this->stockStatusSelectBuilder->buildSelect($select);

        $statusNotNullExpr = $connection->getCheckSql('o.stock_status IS NOT NULL', 'o.stock_status', '0');
        $statusExpr = $this->getStatusExpression($connection);

        $select->columns(
            [
                'status' => $connection->getLeastSql(
                    [
                        new \Zend_Db_Expr('MIN(' . $statusNotNullExpr . ')'),
                        new \Zend_Db_Expr('MIN(' . $statusExpr . ')'),
                    ]
                ),
            ]
        );

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        return $select;
    }

    /**
     * Prepare stock status data in temporary index table
     *
     * @param int|array $entityIds  the product limitation
     * @return $this
     */
    protected function _prepareIndexTable($entityIds = null)
    {
        parent::_prepareIndexTable($entityIds);
        $this->_cleanBundleOptionStockData();

        return $this;
    }

    /**
     * Update Stock status index by product ids
     *
     * @param array|int $entityIds
     *
     * @return $this
     */
    protected function _updateIndex($entityIds)
    {
        parent::_updateIndex($entityIds);
        $this->_cleanBundleOptionStockData();

        return $this;
    }

    /**
     * Clean temporary bundle options stock data
     *
     * @return $this
     */
    protected function _cleanBundleOptionStockData()
    {
        $this->getConnection()->delete($this->_getBundleOptionTable());
        return $this;
    }

    /**
     * Build expression for bundle options stock status
     *
     * @return \Zend_Db_Expr
     */
    private function getOptionsStatusExpression(): \Zend_Db_Expr
    {
        $connection = $this->getConnection();
        $isAvailableExpr = $connection->getCheckSql(
            'bs.selection_can_change_qty = 0 AND bs.selection_qty > i.qty',
            '0',
            'i.stock_status'
        );
        if ($this->stockConfiguration->getBackorders()) {
            $backordersExpr = $connection->getCheckSql(
                'cisi.use_config_backorders = 0 AND cisi.backorders = 0',
                $isAvailableExpr,
                'i.stock_status'
            );
        } else {
            $backordersExpr = $connection->getCheckSql(
                'cisi.use_config_backorders = 0 AND cisi.backorders > 0',
                'i.stock_status',
                $isAvailableExpr
            );
        }
        if ($this->stockConfiguration->getManageStock()) {
            $statusExpr = $connection->getCheckSql(
                'cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0',
                1,
                $backordersExpr
            );
        } else {
            $statusExpr = $connection->getCheckSql(
                'cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1',
                $backordersExpr,
                1
            );
        }

        return new \Zend_Db_Expr(
            'MAX(' . $connection->getCheckSql('e.required_options = 0', $statusExpr, '0') . ')'
        );
    }
}
