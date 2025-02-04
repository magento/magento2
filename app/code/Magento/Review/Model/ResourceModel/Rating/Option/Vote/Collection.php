<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\ResourceModel\Rating\Option\Vote;

/**
 * Rating votes collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Store list manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory
     */
    protected $_ratingCollectionF;

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
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_ratingCollectionF = $ratingCollectionF;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Review\Model\Rating\Option\Vote::class,
            \Magento\Review\Model\ResourceModel\Rating\Option\Vote::class
        );
    }

    /**
     * Set review filter
     *
     * @param int $reviewId
     * @return $this
     */
    public function setReviewFilter($reviewId)
    {
        $this->getSelect()->where("main_table.review_id = ?", $reviewId);
        return $this;
    }

    /**
     * Set EntityPk filter
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityPkFilter($entityId)
    {
        $this->getSelect()->where("entity_pk_value = ?", $entityId);
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
        $this->getSelect()->join(
            ['rstore' => $this->getTable('review_store')],
            $this->getConnection()->quoteInto(
                'main_table.review_id=rstore.review_id AND rstore.store_id=?',
                (int)$storeId
            ),
            []
        );
        return $this;
    }

    /**
     * Add rating info to select
     *
     * @param int $storeId
     * @return $this
     */
    public function addRatingInfo($storeId = null)
    {
        $connection = $this->getConnection();
        $ratingCodeCond = $connection->getIfNullSql('title.value', 'rating.rating_code');
        $this->getSelect()->join(
            ['rating' => $this->getTable('rating')],
            'rating.rating_id = main_table.rating_id',
            ['rating_code']
        )->joinLeft(
            ['title' => $this->getTable('rating_title')],
            $connection->quoteInto(
                'main_table.rating_id=title.rating_id AND title.store_id = ?',
                (int)$this->_storeManager->getStore()->getId()
            ),
            ['rating_code' => $ratingCodeCond]
        );
        if (!$this->_storeManager->isSingleStoreMode()) {
            if ($storeId == null) {
                $storeId = $this->_storeManager->getStore()->getId();
            }

            if (is_array($storeId)) {
                $condition = $connection->prepareSqlCondition('store.store_id', ['in' => $storeId]);
            } else {
                $condition = $connection->quoteInto('store.store_id = ?', $storeId);
            }

            $this->getSelect()->join(
                ['store' => $this->getTable('rating_store')],
                'main_table.rating_id = store.rating_id AND ' . $condition
            );
        }
        $connection->fetchAll($this->getSelect());
        return $this;
    }

    /**
     * Add option info to select
     *
     * @return $this
     */
    public function addOptionInfo()
    {
        $this->getSelect()->join(
            ['rating_option' => $this->getTable('rating_option')],
            'main_table.option_id = rating_option.option_id'
        );
        return $this;
    }

    /**
     * Add rating options
     *
     * @return $this
     */
    public function addRatingOptions()
    {
        if (!$this->getSize()) {
            return $this;
        }
        foreach ($this->getItems() as $item) {
            /** @var \Magento\Review\Model\ResourceModel\Rating\Option\Collection $options */
            $options = $this->_ratingCollectionF->create();
            $options->addRatingFilter($item->getRatingId())->load();

            if ($item->getRatingId()) {
                $item->setRatingOptions($options);
            } else {
                return $this;
            }
        }
        return $this;
    }
}
