<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Review\Model\Resource;

use Magento\Framework\Model\AbstractModel;

/**
 * Review resource model
 */
class Review extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Review table
     *
     * @var string
     */
    protected $_reviewTable;

    /**
     * Review Detail table
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
     * Review aggregate table
     *
     * @var string
     */
    protected $_aggregateTable;

    /**
     * Cache of deleted rating data
     *
     * @var array
     */
    private $_deleteCache = array();

    /**
     * Core date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Rating model
     *
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $_ratingFactory;

    /**
     * Rating resource model
     *
     * @var \Magento\Review\Model\Resource\Rating\Option
     */
    protected $_ratingOptions;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Review\Model\Resource\Rating\Option $ratingOptions
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        Rating\Option $ratingOptions
    ) {
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->_ratingFactory = $ratingFactory;
        $this->_ratingOptions = $ratingOptions;

        parent::__construct($resource);
    }

    /**
     * Define main table. Define other tables name
     *
     * @return void
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
     * @return \Zend_Db_Select
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
            $object->setStores(array($object->getStores(), 0));
        }
        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $adapter = $this->_getWriteAdapter();
        /**
         * save detail
         */
        $detail = array(
            'title' => $object->getTitle(),
            'detail' => $object->getDetail(),
            'nickname' => $object->getNickname()
        );
        $select = $adapter->select()->from($this->_reviewDetailTable, 'detail_id')->where('review_id = :review_id');
        $detailId = $adapter->fetchOne($select, array(':review_id' => $object->getId()));

        if ($detailId) {
            $condition = array("detail_id = ?" => $detailId);
            $adapter->update($this->_reviewDetailTable, $detail, $condition);
        } else {
            $detail['store_id'] = $object->getStoreId();
            $detail['customer_id'] = $object->getCustomerId();
            $detail['review_id'] = $object->getId();
            $adapter->insert($this->_reviewDetailTable, $detail);
        }

        /**
         * save stores
         */
        $stores = $object->getStores();
        if (!empty($stores)) {
            $condition = array('review_id = ?' => $object->getId());
            $adapter->delete($this->_reviewStoreTable, $condition);

            $insertedStoreIds = array();
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert = array('store_id' => $storeId, 'review_id' => $object->getId());
                $adapter->insert($this->_reviewStoreTable, $storeInsert);
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
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->_reviewStoreTable,
            array('store_id')
        )->where(
            'review_id = :review_id'
        );
        $stores = $adapter->fetchCol($select, array(':review_id' => $object->getId()));
        if (empty($stores) && $this->_storeManager->hasSingleStore()) {
            $object->setStores(array($this->_storeManager->getStore(true)->getId()));
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
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        // prepare rating ids, that depend on review
        $this->_deleteCache = array(
            'ratingIds' => $this->_loadVotedRatingIds($object->getId()),
            'entityPkValue' => $object->getEntityPkValue()
        );
        return $this;
    }

    /**
     * Perform actions after object delete
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function afterDeleteCommit(AbstractModel $object)
    {
        $this->aggregate($object);

        // reaggregate ratings, that depended on this review
        $this->_aggregateRatings($this->_deleteCache['ratingIds'], $this->_deleteCache['entityPkValue']);
        $this->_deleteCache = array();

        return $this;
    }

    /**
     * Retrieves total reviews
     *
     * @param int $entityPkValue
     * @param bool $approvedOnly
     * @param int $storeId
     * @return int
     */
    public function getTotalReviews($entityPkValue, $approvedOnly = false, $storeId = 0)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->_reviewTable,
            array('review_count' => new \Zend_Db_Expr('COUNT(*)'))
        )->where(
            "{$this->_reviewTable}.entity_pk_value = :pk_value"
        );
        $bind = array(':pk_value' => $entityPkValue);
        if ($storeId > 0) {
            $select->join(
                array('store' => $this->_reviewStoreTable),
                $this->_reviewTable . '.review_id=store.review_id AND store.store_id = :store_id',
                array()
            );
            $bind[':store_id'] = (int) $storeId;
        }
        if ($approvedOnly) {
            $select->where("{$this->_reviewTable}.status_id = :status_id");
            $bind[':status_id'] = \Magento\Review\Model\Review::STATUS_APPROVED;
        }
        return $adapter->fetchOne($select, $bind);
    }

    /**
     * Aggregate
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    public function aggregate($object)
    {
        $readAdapter = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        if (!$object->getEntityPkValue() && $object->getId()) {
            $object->load($object->getReviewId());
        }

        $ratingModel = $this->_ratingFactory->create();
        $ratingSummaries = $ratingModel->getEntitySummary($object->getEntityPkValue(), false);

        foreach ($ratingSummaries as $ratingSummaryObject) {
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
            $select = $readAdapter->select()->from($this->_aggregateTable)
                ->where('entity_pk_value = :pk_value')
                ->where('entity_type = :entity_type')
                ->where('store_id = :store_id');
            $bind = array(
                ':pk_value' => $object->getEntityPkValue(),
                ':entity_type' => $object->getEntityId(),
                ':store_id' => $ratingSummaryObject->getStoreId()
            );
            $oldData = $readAdapter->fetchRow($select, $bind);

            $data = new \Magento\Framework\Object();

            $data->setReviewsCount($reviewsCount)
                ->setEntityPkValue($object->getEntityPkValue())
                ->setEntityType($object->getEntityId())
                ->setRatingSummary($ratingSummary > 0 ? $ratingSummary : 0)
                ->setStoreId($ratingSummaryObject->getStoreId());

            $writeAdapter->beginTransaction();
            try {
                if ($oldData['primary_id'] > 0) {
                    $condition = array("{$this->_aggregateTable}.primary_id = ?" => $oldData['primary_id']);
                    $writeAdapter->update($this->_aggregateTable, $data->getData(), $condition);
                } else {
                    $writeAdapter->insert($this->_aggregateTable, $data->getData());
                }
                $writeAdapter->commit();
            } catch (\Exception $e) {
                $writeAdapter->rollBack();
            }
        }
    }

    /**
     * Get rating IDs from review votes
     *
     * @param int $reviewId
     * @return array
     */
    protected function _loadVotedRatingIds($reviewId)
    {
        $adapter = $this->_getReadAdapter();
        if (empty($reviewId)) {
            return array();
        }
        $select = $adapter->select()->from(array('v' => $this->getTable('rating_option_vote')), 'r.rating_id')
            ->joinInner(array('r' => $this->getTable('rating')), 'v.rating_id=r.rating_id')
            ->where('v.review_id = :revire_id');
        return $adapter->fetchCol($select, array(':revire_id' => $reviewId));
    }

    /**
     * Aggregate this review's ratings.
     * Useful, when changing the review.
     *
     * @param int[] $ratingIds
     * @param int $entityPkValue
     * @return $this
     */
    protected function _aggregateRatings($ratingIds, $entityPkValue)
    {
        if ($ratingIds && !is_array($ratingIds)) {
            $ratingIds = array((int)$ratingIds);
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
     */
    public function getEntityIdByCode($entityCode)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from($this->_reviewEntityTable, array('entity_id'))
            ->where('entity_code = :entity_code');
        return $adapter->fetchOne($select, array(':entity_code' => $entityCode));
    }

    /**
     * Delete reviews by product id.
     * Better to call this method in transaction, because operation performed on two separated tables
     *
     * @param int $productId
     * @return $this
     */
    public function deleteReviewsByProductId($productId)
    {
        $this->_getWriteAdapter()->delete(
            $this->_reviewTable,
            array(
                'entity_pk_value=?' => $productId,
                'entity_id=?' => $this->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
            )
        );
        $this->_getWriteAdapter()->delete(
            $this->getTable('review_entity_summary'),
            array(
                'entity_pk_value=?' => $productId,
                'entity_type=?' => $this->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
            )
        );
        return $this;
    }
}
