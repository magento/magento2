<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\AbstractIndexer;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ObjectManager;
use Magento\CatalogInventory\Model\Indexer\Stock\Action\Full;

/**
 * CatalogInventory Default Stock Status Indexer Resource Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 */
class DefaultStock extends AbstractIndexer implements StockInterface
{
    /**
     * Current Product Type Id
     *
     * @var string
     */
    protected $_typeId;

    /**
     * Product Type is composite flag
     *
     * @var bool
     */
    protected $_isComposite = false;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var QueryProcessorComposite
     */
    private $queryProcessorComposite;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var \Magento\Indexer\Model\Indexer\StateFactory
     */
    private $indexerStateFactory;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\FrontendResource
     */
    private $indexerStockFrontendResource;

    /**
     * Param for switching logic which depends on action type (full reindex or partial)
     *
     * @var string
     */
    private $actionType;

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
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $connectionName = null,
        \Magento\Indexer\Model\Indexer\StateFactory $stateFactory = null,
        \Magento\Indexer\Model\ResourceModel\FrontendResource $indexerStockFrontendResource = null
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->indexerStateFactory = $stateFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Indexer\Model\Indexer\StateFactory::class);
        $this->indexerStockFrontendResource = $indexerStockFrontendResource ?: ObjectManager::getInstance()
            ->get(\Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\FrontendResource::class);
        parent::__construct($context, $tableStrategy, $eavConfig, $connectionName);
    }

    /**
     * Initialize connection and define main table name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cataloginventory_stock_status', 'product_id');
    }

    /**
     * Reindex all stock status data for default logic product type
     *
     * @return $this
     * @throws \Exception
     */
    public function reindexAll()
    {
        $this->tableStrategy->setUseIdxTable(true);
        $this->beginTransaction();
        try {
            $this->_prepareIndexTable();
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Reindex stock data for defined product ids
     *
     * @param int|array $entityIds
     * @return $this
     */
    public function reindexEntity($entityIds)
    {
        if ($this->getActionType() === Full::ACTION_TYPE) {
            $this->tableStrategy->setUseIdxTable(false);
            $this->_prepareIndexTable($entityIds);
            return $this;
        }

        $this->_updateIndex($entityIds);
        return $this;
    }

    /**
     * Returns action run type
     *
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * Set action run type
     *
     * @param string $type
     * @return $this
     */
    public function setActionType($type)
    {
        $this->actionType = $type;
        return $this;
    }

    /**
     * Set active Product Type Id
     *
     * @param string $typeId
     * @return $this
     */
    public function setTypeId($typeId)
    {
        $this->_typeId = $typeId;
        return $this;
    }

    /**
     * Retrieve active Product Type Id
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTypeId()
    {
        if ($this->_typeId === null) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Undefined product type'));
        }
        return $this->_typeId;
    }

    /**
     * Set Product Type Composite flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setIsComposite($flag)
    {
        $this->_isComposite = (bool) $flag;
        return $this;
    }

    /**
     * Check product type is composite
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsComposite()
    {
        return $this->_isComposite;
    }

    /**
     * Retrieve is Global Manage Stock enabled
     *
     * @return bool
     */
    protected function _isManageStock()
    {
        return $this->_scopeConfig->isSetFlag(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the select object for get stock status by product ids
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $connection = $this->getConnection();
        $qtyExpr = $connection->getCheckSql('cisi.qty > 0', 'cisi.qty', 0);
        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        );
        $select->join(
            ['cis' => $this->getTable('cataloginventory_stock')],
            '',
            ['website_id', 'stock_id']
        )->joinInner(
            ['cisi' => $this->getTable('cataloginventory_stock_item')],
            'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id',
            []
        )->columns(
            ['qty' => $qtyExpr]
        )->where(
            'cis.website_id = ?',
            $this->getStockConfiguration()->getDefaultScopeId()
        )->where('e.type_id = ?', $this->getTypeId())
            ->group(['e.entity_id', 'cis.website_id', 'cis.stock_id']);

        $select->columns(['status' => $this->getStatusExpression($connection, true)]);
        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        return $select;
    }

    /**
     * Prepare stock status data in temporary index table
     *
     * @param int|array $entityIds the product limitation
     * @return $this
     */
    protected function _prepareIndexTable($entityIds = null)
    {
        $connection = $this->getConnection();
        $select = $this->_getStockStatusSelect($entityIds, true);
        $select = $this->getQueryProcessorComposite()->processQuery($select, $entityIds);
        $query = $select->insertFromSelect($this->getIdxTable());
        $connection->query($query);

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
        $connection = $this->getConnection();
        $select = $this->_getStockStatusSelect($entityIds, true);
        $select = $this->getQueryProcessorComposite()->processQuery($select, $entityIds, true);
        $query = $connection->query($select);

        $i = 0;
        $data = [];
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $i++;
            $data[] = [
                'product_id' => (int)$row['entity_id'],
                'website_id' => (int)$row['website_id'],
                'stock_id' => Stock::DEFAULT_STOCK_ID,
                'qty' => (double)$row['qty'],
                'stock_status' => (int)$row['status'],
            ];
            if ($i % 1000 == 0) {
                $this->_updateIndexTable($data);
                $data = [];
            }
        }
        $this->_updateIndexTable($data);

        return $this;
    }

    /**
     * Update stock status index table (INSERT ... ON DUPLICATE KEY UPDATE ...)
     *
     * @param array $data
     * @return $this
     */
    protected function _updateIndexTable($data)
    {
        if (empty($data)) {
            return $this;
        }

        $connection = $this->getConnection();
        $connection->insertOnDuplicate(
            $this->indexerStockFrontendResource->getMainTable(),
            $data,
            ['qty', 'stock_status']
        );

        return $this;
    }

    /**
     * Retrieve temporary index table name
     *
     * @param string $table
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIdxTable($table = null)
    {
        return $this->tableStrategy->getTableName('cataloginventory_stock_status');
    }

    /**
     * @param AdapterInterface $connection
     * @param bool $isAggregate
     * @return mixed
     */
    protected function getStatusExpression(AdapterInterface $connection, $isAggregate = false)
    {
        $isInStockExpression = $isAggregate ? 'MAX(cisi.is_in_stock)' : 'cisi.is_in_stock';
        if ($this->_isManageStock()) {
            $statusExpr = $connection->getCheckSql(
                'cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0',
                1,
                $isInStockExpression
            );
        } else {
            $statusExpr = $connection->getCheckSql(
                'cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1',
                $isInStockExpression,
                1
            );
        }
        return $statusExpr;
    }

    /**
     * @return StockConfigurationInterface
     *
     * @deprecated
     */
    protected function getStockConfiguration()
    {
        if ($this->stockConfiguration === null) {
            $this->stockConfiguration = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\CatalogInventory\Api\StockConfigurationInterface::class);
        }
        return $this->stockConfiguration;
    }

    /**
     * @return QueryProcessorComposite
     */
    private function getQueryProcessorComposite()
    {
        if (null === $this->queryProcessorComposite) {
            $this->queryProcessorComposite = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\QueryProcessorComposite::class);
        }
        return $this->queryProcessorComposite;
    }

    /**
     * @inheritdoc
     */
    public function getMainTable()
    {
        $table = parent::getMainTable();
        $indexerState = $this->indexerStateFactory->create()->loadByIndexer(
            \Magento\CatalogInventory\Model\Indexer\Stock\Processor::INDEXER_ID
        );
        $destinationTableSuffix = ($indexerState->getTableSuffix() === '')
            ? \Magento\Framework\Indexer\StateInterface::ADDITIONAL_TABLE_SUFFIX
            : '';
        return $table . $destinationTableSuffix;
    }
}
