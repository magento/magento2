<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\Resource;

/**
 * Rating resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Rating extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    const RATING_STATUS_APPROVED = 'Approved';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Review\Helper\Data $ratingData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\Resource\Review\Summary $reviewSummary
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Review\Helper\Data $ratingData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\Resource\Review\Summary $reviewSummary
    ) {
        $this->moduleManager = $moduleManager;
        $this->_storeManager = $storeManager;
        $this->_logger = $logger;
        $this->_reviewSummary = $reviewSummary;
        parent::__construct($resource);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rating', 'rating_id');
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [['field' => 'rating_code', 'title' => '']];
        return $this;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Review\Model\Rating $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $adapter = $this->_getReadAdapter();

        $table = $this->getMainTable();
        $storeId = (int)$this->_storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId();
        $select = parent::_getLoadSelect($field, $value, $object);
        $codeExpr = $adapter->getIfNullSql('title.value', "{$table}.rating_code");

        $select->joinLeft(
            ['title' => $this->getTable('rating_title')],
            $adapter->quoteInto("{$table}.rating_id = title.rating_id AND title.store_id = ?", $storeId),
            ['rating_code' => $codeExpr]
        );

        return $select;
    }

    /**
     * Actions after load
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Review\Model\Rating $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterLoad($object);

        if (!$object->getId()) {
            return $this;
        }

        $adapter = $this->_getReadAdapter();
        $bind = [':rating_id' => (int)$object->getId()];
        // load rating titles
        $select = $adapter->select()->from(
            $this->getTable('rating_title'),
            ['store_id', 'value']
        )->where(
            'rating_id=:rating_id'
        );

        $result = $adapter->fetchPairs($select, $bind);
        if ($result) {
            $object->setRatingCodes($result);
        }

        // load rating available in stores
        $object->setStores($this->getStores((int)$object->getId()));

        return $this;
    }

    /**
     * Retrieve store IDs related to given rating
     *
     * @param  int $ratingId
     * @return array
     */
    public function getStores($ratingId)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $this->getTable('rating_store'),
            'store_id'
        )->where(
            'rating_id = ?',
            $ratingId
        );
        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Actions after save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Review\Model\Rating $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);

        $adapter = $this->_getWriteAdapter();
        $ratingId = (int)$object->getId();

        if ($object->hasRatingCodes()) {
            $ratingTitleTable = $this->getTable('rating_title');
            $adapter->beginTransaction();
            try {
                $select = $adapter->select()->from(
                    $ratingTitleTable,
                    ['store_id', 'value']
                )->where(
                    'rating_id = :rating_id'
                );
                $old = $adapter->fetchPairs($select, [':rating_id' => $ratingId]);
                $new = array_filter(array_map('trim', $object->getRatingCodes()));

                $insert = array_diff_assoc($new, $old);
                $delete = array_diff_assoc($old, $new);
                if (!empty($delete)) {
                    $where = ['rating_id = ?' => $ratingId, 'store_id IN(?)' => array_keys($delete)];
                    $adapter->delete($ratingTitleTable, $where);
                }

                if ($insert) {
                    $data = [];
                    foreach ($insert as $storeId => $title) {
                        $data[] = ['rating_id' => $ratingId, 'store_id' => (int)$storeId, 'value' => $title];
                    }
                    if (!empty($data)) {
                        $adapter->insertMultiple($ratingTitleTable, $data);
                    }
                }
                $adapter->commit();
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $adapter->rollBack();
            }
        }

        if ($object->hasStores()) {
            $ratingStoreTable = $this->getTable('rating_store');
            $adapter->beginTransaction();
            try {
                $select = $adapter->select()->from(
                    $ratingStoreTable,
                    ['store_id']
                )->where(
                    'rating_id = :rating_id'
                );
                $old = $adapter->fetchCol($select, [':rating_id' => $ratingId]);
                $new = $object->getStores();

                $insert = array_diff($new, $old);
                $delete = array_diff($old, $new);

                if ($delete) {
                    $where = ['rating_id = ?' => $ratingId, 'store_id IN(?)' => $delete];
                    $adapter->delete($ratingStoreTable, $where);
                }

                if ($insert) {
                    $data = [];
                    foreach ($insert as $storeId) {
                        $data[] = ['rating_id' => $ratingId, 'store_id' => (int)$storeId];
                    }
                    $adapter->insertMultiple($ratingStoreTable, $data);
                }

                $adapter->commit();
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $adapter->rollBack();
            }
        }

        return $this;
    }

    /**
     * Perform actions after object delete
     * Prepare rating data for reaggregate all data for reviews
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterDelete($object);
        if (!$this->moduleManager->isEnabled('Magento_Review')) {
            return $this;
        }
        $data = $this->_getEntitySummaryData($object);
        $summary = [];
        foreach ($data as $row) {
            $clone = clone $object;
            $clone->addData($row);
            $summary[$clone->getStoreId()][$clone->getEntityPkValue()] = $clone;
        }
        $this->_reviewSummary->reAggregate($summary);
        return $this;
    }

    /**
     * Return array of rating summary
     *
     * @param \Magento\Review\Model\Rating $object
     * @param boolean $onlyForCurrentStore
     * @return array
     */
    public function getEntitySummary($object, $onlyForCurrentStore = true)
    {
        $data = $this->_getEntitySummaryData($object);

        if ($onlyForCurrentStore) {
            foreach ($data as $row) {
                if ($row['store_id'] == $this->_storeManager->getStore()->getId()) {
                    $object->addData($row);
                }
            }
            return $object;
        }

        $stores = $this->_storeManager->getStores();

        $result = [];
        foreach ($data as $row) {
            $clone = clone $object;
            $clone->addData($row);
            $result[$clone->getStoreId()] = $clone;
        }

        $usedStoresId = array_keys($result);
        foreach ($stores as $store) {
            if (!in_array($store->getId(), $usedStoresId)) {
                $clone = clone $object;
                $clone->setCount(0);
                $clone->setSum(0);
                $clone->setStoreId($store->getId());
                $result[$store->getId()] = $clone;
            }
        }
        return array_values($result);
    }

    /**
     * Return data of rating summary
     *
     * @param \Magento\Review\Model\Rating $object
     * @return array
     */
    protected function _getEntitySummaryData($object)
    {
        $adapter = $this->_getReadAdapter();

        $sumColumn = new \Zend_Db_Expr("SUM(rating_vote.{$adapter->quoteIdentifier('percent')})");
        $countColumn = new \Zend_Db_Expr("COUNT(*)");

        $select = $adapter->select()->from(
            ['rating_vote' => $this->getTable('rating_option_vote')],
            ['entity_pk_value' => 'rating_vote.entity_pk_value', 'sum' => $sumColumn, 'count' => $countColumn]
        )->join(
            ['review' => $this->getTable('review')],
            'rating_vote.review_id=review.review_id',
            []
        )->joinLeft(
            ['review_store' => $this->getTable('review_store')],
            'rating_vote.review_id=review_store.review_id',
            ['review_store.store_id']
        );
        if (!$this->_storeManager->isSingleStoreMode()) {
            $select->join(
                ['rating_store' => $this->getTable('rating_store')],
                'rating_store.rating_id = rating_vote.rating_id AND rating_store.store_id = review_store.store_id',
                []
            );
        }
        $select->join(
            ['review_status' => $this->getTable('review_status')],
            'review.status_id = review_status.status_id',
            []
        )->where(
            'review_status.status_code = :status_code'
        )->group(
            'rating_vote.entity_pk_value'
        )->group(
            'review_store.store_id'
        );
        $bind = [':status_code' => self::RATING_STATUS_APPROVED];

        $entityPkValue = $object->getEntityPkValue();
        if ($entityPkValue) {
            $select->where('rating_vote.entity_pk_value = :pk_value');
            $bind[':pk_value'] = $entityPkValue;
        }

        return $adapter->fetchAll($select, $bind);
    }

    /**
     * Review summary
     *
     * @param \Magento\Review\Model\Rating $object
     * @param boolean $onlyForCurrentStore
     * @return array
     */
    public function getReviewSummary($object, $onlyForCurrentStore = true)
    {
        $adapter = $this->_getReadAdapter();

        $sumColumn = new \Zend_Db_Expr("SUM(rating_vote.{$adapter->quoteIdentifier('percent')})");
        $countColumn = new \Zend_Db_Expr('COUNT(*)');
        $select = $adapter->select()->from(
            ['rating_vote' => $this->getTable('rating_option_vote')],
            ['sum' => $sumColumn, 'count' => $countColumn]
        )->joinLeft(
            ['review_store' => $this->getTable('review_store')],
            'rating_vote.review_id = review_store.review_id',
            ['review_store.store_id']
        );
        if (!$this->_storeManager->isSingleStoreMode()) {
            $select->join(
                ['rating_store' => $this->getTable('rating_store')],
                'rating_store.rating_id = rating_vote.rating_id AND rating_store.store_id = review_store.store_id',
                []
            );
        }
        $select->where(
            'rating_vote.review_id = :review_id'
        )->group(
            'rating_vote.review_id'
        )->group(
            'review_store.store_id'
        );

        $data = $adapter->fetchAll($select, [':review_id' => $object->getReviewId()]);

        if ($onlyForCurrentStore) {
            foreach ($data as $row) {
                if ($row['store_id'] == $this->_storeManager->getStore()->getId()) {
                    $object->addData($row);
                }
            }
            return $object;
        }

        $result = [];

        $stores = $this->_storeManager->getStore()->getResourceCollection()->load();

        foreach ($data as $row) {
            $clone = clone $object;
            $clone->addData($row);
            $result[$clone->getStoreId()] = $clone;
        }

        $usedStoresId = array_keys($result);

        foreach ($stores as $store) {
            if (!in_array($store->getId(), $usedStoresId)) {
                $clone = clone $object;
                $clone->setCount(0);
                $clone->setSum(0);
                $clone->setStoreId($store->getId());
                $result[$store->getId()] = $clone;
            }
        }

        return array_values($result);
    }

    /**
     * Get rating entity type id by code
     *
     * @param string $entityCode
     * @return int
     */
    public function getEntityIdByCode($entityCode)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $this->getTable('rating_entity'),
            ['entity_id']
        )->where(
            'entity_code = :entity_code'
        );

        return $this->_getReadAdapter()->fetchOne($select, [':entity_code' => $entityCode]);
    }

    /**
     * Delete ratings by product id
     *
     * @param int $productId
     * @return $this
     */
    public function deleteAggregatedRatingsByProductId($productId)
    {
        $entityId = $this->getEntityIdByCode(\Magento\Review\Model\Rating::ENTITY_PRODUCT_CODE);
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()->from($this->getMainTable(), 'rating_id')->where('entity_id = :entity_id');
        $ratingIds = $adapter->fetchCol($select, [':entity_id' => $entityId]);

        if ($ratingIds) {
            $where = ['entity_pk_value = ?' => (int)$productId, 'rating_id IN(?)' => $ratingIds];
            $adapter->delete($this->getTable('rating_option_vote_aggregated'), $where);
        }

        return $this;
    }
}
