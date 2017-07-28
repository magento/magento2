<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Review resource model
 *
 * @api
 * @since 2.0.0
 */
class Review extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Review table
     *
     * @var string
     * @since 2.0.0
     */
    protected $_reviewTable;

    /**
     * Review Detail table
     *
     * @var string
     * @since 2.0.0
     */
    protected $_reviewDetailTable;

    /**
     * Review status table
     *
     * @var string
     * @since 2.0.0
     */
    protected $_reviewStatusTable;

    /**
     * Review entity table
     *
     * @var string
     * @since 2.0.0
     */
    protected $_reviewEntityTable;

    /**
     * Review store table
     *
     * @var string
     * @since 2.0.0
     */
    protected $_reviewStoreTable;

    /**
     * Review aggregate table
     *
     * @var string
     * @since 2.0.0
     */
    protected $_aggregateTable;

    /**
     * Cache of deleted rating data
     *
     * @var array
     * @since 2.0.0
     */
    private $_deleteCache = [];

    /**
     * Core date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.0.0
     */
    protected $_date;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Rating model
     *
     * @var \Magento\Review\Model\RatingFactory
     * @since 2.0.0
     */
    protected $_ratingFactory;

    /**
     * Rating resource model
     *
     * @var \Magento\Review\Model\ResourceModel\Rating\Option
     * @since 2.0.0
     */
    protected $_ratingOptions;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Review\Model\ResourceModel\Rating\Option $ratingOptions
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        Rating\Option $ratingOptions,
        $connectionName = null
    ) {
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->_ratingFactory = $ratingFactory;
        $this->_ratingOptions = $ratingOptions;

        parent::__construct($context, $connectionName);
    }

    /**
     * Define main table. Define other tables name
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('review', 'review_id');
        $this->_reviewTable = $this->getTable('review');
        $this->_reviewDetailTable = $this->getTable('review_detail');
        $this->_reviewStatusTable = $this->getTable('review_status');
        $this->_reviewEntityTable = $this->getTable('review_entity');
        $this->_reviewStoreTable = $this->getTable('review_store');
        $this->_aggregateTable = $this->getTable('review_entity_summary');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return \Magento\Framework\DB\Select
     * @since 2.0.0
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->join(
            $this->_reviewDetailTable,
            $this->getMainTable() . ".review_id = {$this->_reviewDetailTable}.review_id"
        );
        return $select;
    }

    /**
     * Perform actions before object save
     *
     * @param AbstractModel $object
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (!$object->getId()) {
            $object->setCreatedAt($this->_date->gmtDate());
        }
        if ($object->hasData('stores') && is_array($object->getStores())) {
            $stores = $object->getStores();
            $stores[] = 0;
            $object->setStores($stores);
        } elseif ($object->hasData('stores')) {
            $object->setStores([$object->getStores(), 0]);
        }
        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @since 2.0.0
     */
    protected function _afterSave(AbstractModel $object)
    {
        $connection = $this->getConnection();
        /**
         * save detail
         */
        $detail = [
            'title' => $object->getTitle(),
            'detail' => $object->getDetail(),
            'nickname' => $object->getNickname(),
        ];
        $select = $connection->select()->from($this->_reviewDetailTable, 'detail_id')->where('review_id = :review_id');
        $detailId = $connection->fetchOne($select, [':review_id' => $object->getId()]);

        if ($detailId) {
            $condition = ["detail_id = ?" => $detailId];
            $connection->update($this->_reviewDetailTable, $detail, $condition);
        } else {
            $detail['store_id'] = $object->getStoreId();
            $detail['customer_id'] = $object->getCustomerId();
            $detail['review_id'] = $object->getId();
            $connection->insert($this->_reviewDetailTable, $detail);
        }

        /**
         * save stores
         */
        $stores = $object->getStores();
        if (!empty($stores)) {
            $condition = ['review_id = ?' => $object->getId()];
            $connection->delete($this->_reviewStoreTable, $condition);

            $insertedStoreIds = [];
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert = ['store_id' => $storeId, 'review_id' => $object->getId()];
                $connection->insert($this->_reviewStoreTable, $storeInsert);
            }
        }

        // reaggregate ratings, that depend on this review
        $this->_aggregateRatings($this->_loadVotedRatingIds($object->getId()), $object->getEntityPkValue());

        return $this;
    }

    /**
     * Perform actions after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @since 2.0.0
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->_reviewStoreTable,
            ['store_id']
        )->where(
            'review_id = :review_id'
        );
        $stores = $connection->fetchCol($select, [':review_id' => $object->getId()]);
        if (empty($stores) && $this->_storeManager->hasSingleStore()) {
            $object->setStores([$this->_storeManager->getStore(true)->getId()]);
        } else {
            $object->setStores($stores);
        }
        return $this;
    }

    /**
     * Action before delete
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        // prepare rating ids, that depend on review
        $this->_deleteCache = [
            'ratingIds' => $this->_loadVotedRatingIds($object->getId()),
            'entityPkValue' => $object->getEntityPkValue(),
        ];
        return $this;
    }

    /**
     * Perform actions after object delete
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @since 2.0.0
     */
    public function afterDeleteCommit(AbstractModel $object)
    {
        $this->aggregate($object);

        // reaggregate ratings, that depended on this review
        $this->_aggregateRatings($this->_deleteCache['ratingIds'], $this->_deleteCache['entityPkValue']);
        $this->_deleteCache = [];

        return $this;
    }

    /**
     * Retrieves total reviews
     *
     * @param int $entityPkValue
     * @param bool $approvedOnly
     * @param int $storeId
     * @return int
     * @since 2.0.0
     */
    public function getTotalReviews($entityPkValue, $approvedOnly = false, $storeId = 0)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->_reviewTable,
            ['review_count' => new \Zend_Db_Expr('COUNT(*)')]
        )->where(
            "{$this->_reviewTable}.entity_pk_value = :pk_value"
        );
        $bind = [':pk_value' => $entityPkValue];
        if ($storeId > 0) {
            $select->join(
                ['store' => $this->_reviewStoreTable],
                $this->_reviewTable . '.review_id=store.review_id AND store.store_id = :store_id',
                []
            );
            $bind[':store_id'] = (int) $storeId;
        }
        if ($approvedOnly) {
            $select->where("{$this->_reviewTable}.status_id = :status_id");
            $bind[':status_id'] = \Magento\Review\Model\Review::STATUS_APPROVED;
        }
        return $connection->fetchOne($select, $bind);
    }

    /**
     * Aggregate
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @since 2.0.0
     */
    public function aggregate($object)
    {
        if (!$object->getEntityPkValue() && $object->getId()) {
            $object->load($object->getReviewId());
        }

        $ratingModel = $this->_ratingFactory->create();
        $ratingSummaries = $ratingModel->getEntitySummary($object->getEntityPkValue(), false);

        foreach ($ratingSummaries as $ratingSummaryObject) {
            $this->aggregateReviewSummary($object, $ratingSummaryObject);
        }
    }

    /**
     * Aggregate review summary
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param \Magento\Review\Model\Rating $ratingSummaryObject
     * @return void
     * @since 2.0.0
     */
    protected function aggregateReviewSummary($object, $ratingSummaryObject)
    {
        $connection = $this->getConnection();

        if ($ratingSummaryObject->getCount()) {
            $ratingSummary = round($ratingSummaryObject->getSum() / $ratingSummaryObject->getCount());
        } else {
            $ratingSummary = $ratingSummaryObject->getSum();
        }

        $reviewsCount = $this->getTotalReviews(
            $object->getEntityPkValue(),
            true,
            $ratingSummaryObject->getStoreId()
        );
        $select = $connection->select()->from($this->_aggregateTable)
            ->where('entity_pk_value = :pk_value')
            ->where('entity_type = :entity_type')
            ->where('store_id = :store_id');
        $bind = [
            ':pk_value' => $object->getEntityPkValue(),
            ':entity_type' => $object->getEntityId(),
            ':store_id' => $ratingSummaryObject->getStoreId(),
        ];
        $oldData = $connection->fetchRow($select, $bind);
        $data = new \Magento\Framework\DataObject();

        $data->setReviewsCount($reviewsCount)
            ->setEntityPkValue($object->getEntityPkValue())
            ->setEntityType($object->getEntityId())
            ->setRatingSummary($ratingSummary > 0 ? $ratingSummary : 0)
            ->setStoreId($ratingSummaryObject->getStoreId());

        $this->writeReviewSummary($oldData, $data);
    }

    /**
     * Write rating summary
     *
     * @param array|bool $oldData
     * @param \Magento\Framework\DataObject $data
     * @return void
     * @since 2.0.0
     */
    protected function writeReviewSummary($oldData, \Magento\Framework\DataObject $data)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            if (isset($oldData['primary_id']) && $oldData['primary_id'] > 0) {
                $condition = ["{$this->_aggregateTable}.primary_id = ?" => $oldData['primary_id']];
                $connection->update($this->_aggregateTable, $data->getData(), $condition);
            } else {
                $connection->insert($this->_aggregateTable, $data->getData());
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
        }
    }

    /**
     * Get rating IDs from review votes
     *
     * @param int $reviewId
     * @return array
     * @since 2.0.0
     */
    protected function _loadVotedRatingIds($reviewId)
    {
        $connection = $this->getConnection();
        if (empty($reviewId)) {
            return [];
        }
        $select = $connection->select()->from(['v' => $this->getTable('rating_option_vote')], 'r.rating_id')
            ->joinInner(['r' => $this->getTable('rating')], 'v.rating_id=r.rating_id')
            ->where('v.review_id = :revire_id');
        return $connection->fetchCol($select, [':revire_id' => $reviewId]);
    }

    /**
     * Aggregate this review's ratings.
     * Useful, when changing the review.
     *
     * @param int[] $ratingIds
     * @param int $entityPkValue
     * @return $this
     * @since 2.0.0
     */
    protected function _aggregateRatings($ratingIds, $entityPkValue)
    {
        if ($ratingIds && !is_array($ratingIds)) {
            $ratingIds = [(int)$ratingIds];
        }
        if ($ratingIds && $entityPkValue) {
            foreach ($ratingIds as $ratingId) {
                $this->_ratingOptions->aggregateEntityByRatingId($ratingId, $entityPkValue);
            }
        }
        return $this;
    }

    /**
     * Reaggregate this review's ratings.
     *
     * @param int $reviewId
     * @param int $entityPkValue
     * @return void
     * @since 2.0.0
     */
    public function reAggregateReview($reviewId, $entityPkValue)
    {
        $this->_aggregateRatings($this->_loadVotedRatingIds($reviewId), $entityPkValue);
    }

    /**
     * Get review entity type id by code
     *
     * @param string $entityCode
     * @return int|bool
     * @since 2.0.0
     */
    public function getEntityIdByCode($entityCode)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->_reviewEntityTable, ['entity_id'])
            ->where('entity_code = :entity_code');
        return $connection->fetchOne($select, [':entity_code' => $entityCode]);
    }

    /**
     * Delete reviews by product id.
     * Better to call this method in transaction, because operation performed on two separated tables
     *
     * @param int $productId
     * @return $this
     * @since 2.0.0
     */
    public function deleteReviewsByProductId($productId)
    {
        $this->getConnection()->delete(
            $this->_reviewTable,
            [
                'entity_pk_value=?' => $productId,
                'entity_id=?' => $this->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
            ]
        );
        $this->getConnection()->delete(
            $this->getTable('review_entity_summary'),
            [
                'entity_pk_value=?' => $productId,
                'entity_type=?' => $this->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
            ]
        );
        return $this;
    }
}
