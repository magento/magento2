<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\ResourceModel\Query;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Helper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Search query collection
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends AbstractCollection
{
    /**
     * Store for filter
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Search resource helper
     *
     * @var Helper
     */
    protected $_resourceHelper;

    /**
     * Constructor
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Helper $resourceHelper
     * @param AdapterInterface $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Helper $resourceHelper,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Init model for collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Search\Model\Query::class, \Magento\Search\Model\ResourceModel\Query::class);
    }

    /**
     * Set Store ID for filter
     *
     * @param Store|int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        if ($store instanceof Store) {
            $store = $store->getId();
        }
        $this->_storeId = $store;
        return $this;
    }

    /**
     * Retrieve Store ID Filter
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Set search query text to filter
     *
     * @param string $query
     * @return $this
     */
    public function setQueryFilter($query)
    {
        $this->getSelect()->reset(
            \Magento\Framework\DB\Select::FROM
        )->distinct(
            true
        )->from(
            ['main_table' => $this->getTable('search_query')]
        )->where(
            'num_results > 0 AND display_in_terms = 1 AND query_text LIKE ?',
            $this->_resourceHelper->addLikeEscape($query, ['position' => 'start'])
        )->order(
            'popularity ' . \Magento\Framework\DB\Select::SQL_DESC
        );
        if ($this->getStoreId()) {
            $this->getSelect()->where('store_id = ?', (int)$this->getStoreId());
        }
        return $this;
    }

    /**
     * Set Popular Search Query Filter
     *
     * @param int|array $storeIds
     * @return $this
     */
    public function setPopularQueryFilter($storeIds = null)
    {
        $this->getSelect()->reset(
            \Magento\Framework\DB\Select::FROM
        )->reset(
            \Magento\Framework\DB\Select::COLUMNS
        )->distinct(
            true
        )->from(
            ['main_table' => $this->getTable('search_query')]
        );

        $storeIds = $storeIds ?: $this->_storeManager->getStore()->getId();
        $this->addStoreFilter($storeIds);
        $this->getSelect()->where('num_results > 0');

        $this->getSelect()->order(['popularity desc']);

        return $this;
    }

    /**
     * Determines whether a Search Term belongs to the top results for given storeId
     *
     * @param string $term
     * @param int $storeId
     * @param int $maxCountCacheableSearchTerms
     * @return bool
     * @since 101.1.0
     */
    public function isTopSearchResult(string $term, int $storeId, int $maxCountCacheableSearchTerms):bool
    {
        $select = $this->getSelect();
        $select->reset(\Magento\Framework\DB\Select::FROM);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->distinct(true);
        $select->from(['main_table' => $this->getTable('search_query')], ['query_text']);
        $select->where('main_table.store_id IN (?)', $storeId);
        $select->where('num_results > 0');
        $select->order(['popularity desc']);

        $select->limit($maxCountCacheableSearchTerms);

        $subQuery = new \Zend_Db_Expr('(' . $select->assemble() . ')');

        $select->reset();
        $select->from(['result' =>  $subQuery ], []);
        $select->where('result.query_text = ?', $term);

        return $this->getSize() > 0;
    }

    /**
     * Set Recent Queries Order
     *
     * @return $this
     */
    public function setRecentQueryFilter()
    {
        $this->setOrder('updated_at', 'desc');
        return $this;
    }

    /**
     * Filter collection by specified store ids
     *
     * @param array|int $storeIds
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        $condition = is_array($storeIds) ? 'main_table.store_id IN (?)' : 'main_table.store_id = ?';
        $this->getSelect()->where($condition, $storeIds);

        return $this;
    }
}
