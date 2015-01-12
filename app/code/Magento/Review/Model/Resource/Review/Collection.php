<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\Resource\Review;

/**
 * Review collection resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Review table
     *
     * @var string
     */
    protected $_reviewTable;

    /**
     * Review detail table
     *
     * @var string
     */
    protected $_reviewDetailTable;

    /**
     * Review status table
     *
     * @var string
     */
    protected $_reviewStatusTable;

    /**
     * Review entity table
     *
     * @var string
     */
    protected $_reviewEntityTable;

    /**
     * Review store table
     *
     * @var string
     */
    protected $_reviewStoreTable;

    /**
     * Add store data flag
     * @var bool
     */
    protected $_addStoreDataFlag = false;

    /**
     * Review data
     *
     * @var \Magento\Review\Helper\Data
     */
    protected $_reviewData = null;

    /**
     * Rating option model
     *
     * @var \Magento\Review\Model\Rating\Option\VoteFactory
     */
    protected $_voteFactory;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Review\Helper\Data $reviewData
     * @param \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Review\Helper\Data $reviewData,
        \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_reviewData = $reviewData;
        $this->_voteFactory = $voteFactory;
        $this->_storeManager = $storeManager;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define module
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Review\Model\Review', 'Magento\Review\Model\Resource\Review');
        $this->_reviewTable = $this->getTable('review');
        $this->_reviewDetailTable = $this->getTable('review_detail');
        $this->_reviewStatusTable = $this->getTable('review_status');
        $this->_reviewEntityTable = $this->getTable('review_entity');
        $this->_reviewStoreTable = $this->getTable('review_store');
    }

    /**
     * Initialize select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->join(
            ['detail' => $this->_reviewDetailTable],
            'main_table.review_id = detail.review_id',
            ['detail_id', 'title', 'detail', 'nickname', 'customer_id']
        );
        return $this;
    }

    /**
     * Add customer filter
     *
     * @param int|string $customerId
     * @return $this
     */
    public function addCustomerFilter($customerId)
    {
        $this->addFilter('customer', $this->getConnection()->quoteInto('detail.customer_id=?', $customerId), 'string');
        return $this;
    }

    /**
     * Add store filter
     *
     * @param int|int[] $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $inCond = $this->getConnection()->prepareSqlCondition('store.store_id', ['in' => $storeId]);
        $this->getSelect()->join(
            ['store' => $this->_reviewStoreTable],
            'main_table.review_id=store.review_id',
            []
        );
        $this->getSelect()->where($inCond);
        return $this;
    }

    /**
     * Add stores data
     *
     * @return $this
     */
    public function addStoreData()
    {
        $this->_addStoreDataFlag = true;
        return $this;
    }

    /**
     * Add entity filter
     *
     * @param int|string $entity
     * @param int $pkValue
     * @return $this
     */
    public function addEntityFilter($entity, $pkValue)
    {
        if (is_numeric($entity)) {
            $this->addFilter('entity', $this->getConnection()->quoteInto('main_table.entity_id=?', $entity), 'string');
        } elseif (is_string($entity)) {
            $this->_select->join(
                $this->_reviewEntityTable,
                'main_table.entity_id=' . $this->_reviewEntityTable . '.entity_id',
                ['entity_code']
            );

            $this->addFilter(
                'entity',
                $this->getConnection()->quoteInto($this->_reviewEntityTable . '.entity_code=?', $entity),
                'string'
            );
        }

        $this->addFilter(
            'entity_pk_value',
            $this->getConnection()->quoteInto('main_table.entity_pk_value=?', $pkValue),
            'string'
        );

        return $this;
    }

    /**
     * Add status filter
     *
     * @param int|string $status
     * @return $this
     */
    public function addStatusFilter($status)
    {
        if (is_string($status)) {
            $statuses = array_flip($this->_reviewData->getReviewStatuses());
            $status = isset($statuses[$status]) ? $statuses[$status] : 0;
        }
        if (is_numeric($status)) {
            $this->addFilter('status', $this->getConnection()->quoteInto('main_table.status_id=?', $status), 'string');
        }
        return $this;
    }

    /**
     * Set date order
     *
     * @param string $dir
     * @return $this
     */
    public function setDateOrder($dir = 'DESC')
    {
        $this->setOrder('main_table.created_at', $dir);
        return $this;
    }

    /**
     * Add rate votes
     *
     * @return $this
     */
    public function addRateVotes()
    {
        foreach ($this->getItems() as $item) {
            $votesCollection = $this->_voteFactory->create()->getResourceCollection()->setReviewFilter(
                $item->getId()
            )->setStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->addRatingInfo(
                $this->_storeManager->getStore()->getId()
            )->load();
            $item->setRatingVotes($votesCollection);
        }

        return $this;
    }

    /**
     * Add reviews total count
     *
     * @return $this
     */
    public function addReviewsTotalCount()
    {
        $this->_select->joinLeft(
            ['r' => $this->_reviewTable],
            'main_table.entity_pk_value = r.entity_pk_value',
            ['total_reviews' => new \Zend_Db_Expr('COUNT(r.review_id)')]
        )->group(
            'main_table.review_id'
        );

        return $this;
    }

    /**
     * Load data
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        $this->_eventManager->dispatch('review_review_collection_load_before', ['collection' => $this]);
        parent::load($printQuery, $logQuery);
        if ($this->_addStoreDataFlag) {
            $this->_addStoreData();
        }
        return $this;
    }

    /**
     * Add store data
     *
     * @return void
     */
    protected function _addStoreData()
    {
        $adapter = $this->getConnection();

        $reviewsIds = $this->getColumnValues('review_id');
        $storesToReviews = [];
        if (count($reviewsIds) > 0) {
            $inCond = $adapter->prepareSqlCondition('review_id', ['in' => $reviewsIds]);
            $select = $adapter->select()->from($this->_reviewStoreTable)->where($inCond);
            $result = $adapter->fetchAll($select);
            foreach ($result as $row) {
                if (!isset($storesToReviews[$row['review_id']])) {
                    $storesToReviews[$row['review_id']] = [];
                }
                $storesToReviews[$row['review_id']][] = $row['store_id'];
            }
        }

        foreach ($this as $item) {
            if (isset($storesToReviews[$item->getId()])) {
                $item->setStores($storesToReviews[$item->getId()]);
            } else {
                $item->setStores([]);
            }
        }
    }
}
