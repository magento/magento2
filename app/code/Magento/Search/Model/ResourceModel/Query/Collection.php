<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\ResourceModel\Query;

use Magento\Store\Model\Store;

/**
 * Search query collection
 *
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Search resource helper
     *
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
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
        if ($storeIds) {
            $this->addStoreFilter($storeIds);
            $this->getSelect()->where('num_results > 0');
        } elseif (null === $storeIds) {
            $this->addStoreFilter($this->_storeManager->getStore()->getId());
            $this->getSelect()->where('num_results > 0');
        }

        $this->getSelect()->order(['popularity desc']);

        return $this;
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
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        $this->getSelect()->where('main_table.store_id IN (?)', $storeIds);
        return $this;
    }
}
