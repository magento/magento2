<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\CatalogInventory\Model\Indexer\Stock\Action\Full;
use Magento\Framework\App\ObjectManager;

/**
 * Bundle Stock Status Indexer Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Stock extends \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock
{
    /**
     * @var \Magento\Indexer\Model\ResourceModel\FrontendResource
     */
    private $indexerStockFrontendResource;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Indexer\StockStatusSelectBuilder
     */
    private $stockStatusSelectBuilder;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Indexer\BundleOptionStockDataSelectBuilder
     */
    private $bundleOptionStockDataSelectBuilder;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $connectionName
     * @param null|\Magento\Indexer\Model\Indexer\StateFactory $stateFactory
     * @param null|\Magento\Indexer\Model\ResourceModel\FrontendResource $indexerStockFrontendResource
     * @param null|\Magento\Bundle\Model\ResourceModel\Indexer\StockStatusSelectBuilder $stockStatusSelectBuilder
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $connectionName = null,
        \Magento\Indexer\Model\Indexer\StateFactory $stateFactory = null,
        \Magento\Indexer\Model\ResourceModel\FrontendResource $indexerStockFrontendResource = null,
        StockStatusSelectBuilder $stockStatusSelectBuilder = null,
        BundleOptionStockDataSelectBuilder $bundleOptionStockDataSelectBuilder = null
    ) {
        parent::__construct($context, $tableStrategy, $eavConfig, $scopeConfig, $connectionName, $stateFactory);

        $this->indexerStockFrontendResource = $indexerStockFrontendResource ?: ObjectManager::getInstance()
            ->get(\Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\FrontendResource::class);

        $this->stockStatusSelectBuilder = $stockStatusSelectBuilder ?: ObjectManager::getInstance()
            ->get(StockStatusSelectBuilder::class);

        $this->bundleOptionStockDataSelectBuilder = $bundleOptionStockDataSelectBuilder ?: ObjectManager::getInstance()
            ->get(BundleOptionStockDataSelectBuilder::class);
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
     * @return $this
     */
    protected function _prepareBundleOptionStockData($entityIds = null, $usePrimaryTable = false)
    {
        $this->_cleanBundleOptionStockData();
        $connection = $this->getConnection();
        $table = $this->getActionType() === Full::ACTION_TYPE
            ? $this->getMainTable()
            : $this->indexerStockFrontendResource->getMainTable();
        $idxTable = $usePrimaryTable ? $table : $this->getIdxTable();
        $select = $this->bundleOptionStockDataSelectBuilder->buildSelect($idxTable);

        $status = new \Zend_Db_Expr(
            'MAX('
            . $connection->getCheckSql('e.required_options = 0', 'i.stock_status', '0')
            . ')'
        );

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
}
