<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\ResourceModel\Rating;

/**
 * Rating collection resource model
 *
 * @api
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory
     */
    protected $_ratingCollectionF;

    /**
     * Add store data flag
     * @var bool
     */
    protected $_addStoreDataFlag = false;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $ratingCollectionF
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $ratingCollectionF,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_ratingCollectionF = $ratingCollectionF;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @var bool
     */
    protected $_isStoreJoined = false;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Review\Model\Rating::class, \Magento\Review\Model\ResourceModel\Rating::class);
    }

    /**
     * Add entity filter
     *
     * @param   int|string $entity
     * @return  $this
     */
    public function addEntityFilter($entity)
    {
        $connection = $this->getConnection();

        $this->getSelect()->join(
            $this->getTable('rating_entity'),
            'main_table.entity_id=' . $this->getTable('rating_entity') . '.entity_id',
            ['entity_code']
        );

        if (is_numeric($entity)) {
            $this->addFilter(
                'entity',
                $connection->quoteInto($this->getTable('rating_entity') . '.entity_id=?', $entity),
                'string'
            );
        } elseif (is_string($entity)) {
            $this->addFilter(
                'entity',
                $connection->quoteInto($this->getTable('rating_entity') . '.entity_code=?', $entity),
                'string'
            );
        }
        return $this;
    }

    /**
     * Set order by position field
     *
     * @param   string $dir
     * @return  $this
     */
    public function setPositionOrder($dir = 'ASC')
    {
        $this->setOrder('main_table.position', $dir);
        return $this;
    }

    /**
     * Set store filter
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreFilter($storeId)
    {
        if ($this->_storeManager->isSingleStoreMode()) {
            return $this;
        }
        $connection = $this->getConnection();
        if (!is_array($storeId)) {
            $storeId = [$storeId === null ? -1 : $storeId];
        }
        if (empty($storeId)) {
            return $this;
        }
        if (!$this->_isStoreJoined) {
            $this->getSelect()->distinct(
                true
            )->join(
                ['store' => $this->getTable('rating_store')],
                'main_table.rating_id = store.rating_id',
                []
            );
            $this->_isStoreJoined = true;
        }
        $inCondition = $connection->prepareSqlCondition('store.store_id', ['in' => $storeId]);
        $this->getSelect()->where($inCondition);
        $this->setPositionOrder();
        return $this;
    }

    /**
     * Add options to ratings in collection
     *
     * @return $this
     */
    public function addOptionToItems()
    {
        $arrRatingId = $this->getColumnValues('rating_id');

        if (!empty($arrRatingId)) {
            /** @var \Magento\Review\Model\ResourceModel\Rating\Option\Collection $collection */
            $collection = $this->_ratingCollectionF->create()->addRatingFilter(
                $arrRatingId
            )->setPositionOrder()->load();

            foreach ($this as $rating) {
                $rating->setOptions($collection->getItemsByColumnValue('rating_id', $rating->getId()));
            }
        }

        return $this;
    }

    /**
     * Add entity summary to item
     *
     * @param int $entityPkValue
     * @param int $storeId
     * @return $this
     */
    public function addEntitySummaryToItem($entityPkValue, $storeId)
    {
        $arrRatingId = $this->getColumnValues('rating_id');
        if (count($arrRatingId) == 0) {
            return $this;
        }

        $connection = $this->getConnection();

        $inCond = $connection->prepareSqlCondition('rating_option_vote.rating_id', ['in' => $arrRatingId]);
        $sumCond = new \Zend_Db_Expr("SUM(rating_option_vote.{$connection->quoteIdentifier('percent')})");
        $countCond = new \Zend_Db_Expr('COUNT(*)');
        $select = $connection->select()->from(
            ['rating_option_vote' => $this->getTable('rating_option_vote')],
            ['rating_id' => 'rating_option_vote.rating_id', 'sum' => $sumCond, 'count' => $countCond]
        )->join(
            ['review_store' => $this->getTable('review_store')],
            'rating_option_vote.review_id=review_store.review_id AND review_store.store_id = :store_id',
            []
        );
        if (!$this->_storeManager->isSingleStoreMode()) {
            $select->join(
                ['rst' => $this->getTable('rating_store')],
                'rst.rating_id = rating_option_vote.rating_id AND rst.store_id = :rst_store_id',
                []
            );
        }
        $select->join(
            ['review' => $this->getTable('review')],
            'review_store.review_id=review.review_id AND review.status_id=1',
            []
        )->where(
            $inCond
        )->where(
            'rating_option_vote.entity_pk_value=:pk_value'
        )->group(
            'rating_option_vote.rating_id'
        );
        $bind = [':store_id' => (int)$storeId, ':pk_value' => $entityPkValue];
        if (!$this->_storeManager->isSingleStoreMode()) {
            $bind[':rst_store_id'] = (int)$storeId;
        }

        $data = $this->getConnection()->fetchAll($select, $bind);

        foreach ($data as $item) {
            $rating = $this->getItemById($item['rating_id']);
            if ($rating && $item['count'] > 0) {
                $rating->setSummary($item['sum'] / $item['count']);
            }
        }
        return $this;
    }

    /**
     * Add rating store name
     *
     * @param int $storeId
     * @return $this
     */
    public function addRatingPerStoreName($storeId)
    {
        $connection = $this->getConnection();
        $ratingCodeCond = $connection->getIfNullSql('title.value', 'main_table.rating_code');
        $this->getSelect()->joinLeft(
            ['title' => $this->getTable('rating_title')],
            $connection->quoteInto('main_table.rating_id=title.rating_id AND title.store_id = ?', (int)$storeId),
            ['rating_code' => $ratingCodeCond]
        );
        return $this;
    }

    /**
     * Add stores data to collection
     *
     * @return $this
     */
    public function addStoreData()
    {
        if (!$this->_storeManager->isSingleStoreMode()) {
            if (!$this->_isCollectionLoaded) {
                $this->_addStoreDataFlag = true;
            } elseif (!$this->_addStoreDataFlag) {
                $this->_addStoreData();
            }
        }

        return $this;
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        $this->_eventManager->dispatch('rating_rating_collection_load_before', ['collection' => $this]);
        parent::load($printQuery, $logQuery);
        if ($this->_addStoreDataFlag) {
            $this->_addStoreData();
        }
        return $this;
    }

    /**
     * Add store data
     *
     * @return $this
     */
    protected function _addStoreData()
    {
        $ratingIds = [];
        foreach ($this as $item) {
            $ratingIds[] = $item->getId();
            $item->setStores([]);
        }
        if (!$ratingIds) {
            return $this;
        }
        $connection = $this->getConnection();

        $inCondition = $connection->prepareSqlCondition('rating_id', ['in' => $ratingIds]);

        $this->_select = $connection->select()->from($this->getTable('rating_store'))->where($inCondition);

        $data = $connection->fetchAll($this->_select);
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $row) {
                $item = $this->getItemById($row['rating_id']);
                $item->setStores(array_merge($item->getStores(), [$row['store_id']]));
            }
        }
        return $this;
    }

    /**
     * Set Active Filter
     *
     * @param bool $isActive
     * @return $this
     */
    public function setActiveFilter($isActive = true)
    {
        $this->getSelect()->where('main_table.is_active=?', $isActive);
        return $this;
    }
}
