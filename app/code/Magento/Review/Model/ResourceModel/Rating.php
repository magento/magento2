<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\ResourceModel;

/**
 * Rating resource model
 *
 * @api
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Rating extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\ResourceModel\Review\Summary $reviewSummary
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ResourceModel\Review\Summary $reviewSummary,
        $connectionName = null
    ) {
        $this->moduleManager = $moduleManager;
        $this->_storeManager = $storeManager;
        $this->_logger = $logger;
        $this->_reviewSummary = $reviewSummary;
        parent::__construct($context, $connectionName);
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
        $connection = $this->getConnection();

        $table = $this->getMainTable();
        $storeId = (int)$this->_storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId();
        $select = parent::_getLoadSelect($field, $value, $object);
        $codeExpr = $connection->getIfNullSql('title.value', "{$table}.rating_code");

        $select->joinLeft(
            ['title' => $this->getTable('rating_title')],
            $connection->quoteInto("{$table}.rating_id = title.rating_id AND title.store_id = ?", $storeId),
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

        $connection = $this->getConnection();
        $bind = [':rating_id' => (int)$object->getId()];
        // load rating titles
        $select = $connection->select()->from(
            $this->getTable('rating_title'),
            ['store_id', 'value']
        )->where(
            'rating_id=:rating_id'
        );

        $result = $connection->fetchPairs($select, $bind);
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
        $select = $this->getConnection()->select()->from(
            $this->getTable('rating_store'),
            'store_id'
        )->where(
            'rating_id = ?',
            $ratingId
        );
        return $this->getConnection()->fetchCol($select);
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
        if ($object->hasRatingCodes()) {
            $this->processRatingCodes($object);
        }

        if ($object->hasStores()) {
            $this->processRatingStores($object);
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function processRatingCodes(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();
        $ratingId = (int)$object->getId();
        $table = $this->getTable('rating_title');
        $select = $connection->select()->from($table, ['store_id', 'value'])
            ->where('rating_id = :rating_id');
        $old = $connection->fetchPairs($select, [':rating_id' => $ratingId]);
        $new = array_filter(array_map('trim', $object->getRatingCodes()));
        $this->deleteRatingData($ratingId, $table, array_keys(array_diff_assoc($old, $new)));

        $insert = [];
        foreach (array_diff_assoc($new, $old) as $storeId => $title) {
            $insert[] = ['rating_id' => $ratingId, 'store_id' => (int)$storeId, 'value' => $title];
        }
        $this->insertRatingData($table, $insert);
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function processRatingStores(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();
        $ratingId = (int)$object->getId();
        $table = $this->getTable('rating_store');
        $select = $connection->select()->from($table, ['store_id'])
            ->where('rating_id = :rating_id');
        $old = $connection->fetchCol($select, [':rating_id' => $ratingId]);
        $new = $object->getStores();
        $this->deleteRatingData($ratingId, $table, array_diff($old, $new));

        $insert = [];
        foreach (array_diff($new, $old) as $storeId) {
            $insert[] = ['rating_id' => $ratingId, 'store_id' => (int)$storeId];
        }
        $this->insertRatingData($table, $insert);
        return $this;
    }

    /**
     * @param int $ratingId
     * @param string $table
     * @param array $storeIds
     * @return void
     */
    protected function deleteRatingData($ratingId, $table, array $storeIds)
    {
        if (empty($storeIds)) {
            return;
        }
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $where = ['rating_id = ?' => $ratingId, 'store_id IN(?)' => $storeIds];
            $connection->delete($table, $where);
            $connection->commit();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $connection->rollBack();
        }
    }

    /**
     * @param string $table
     * @param array $data
     * @return void
     */
    protected function insertRatingData($table, array $data)
    {
        if (empty($data)) {
            return;
        }
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $connection->insertMultiple($table, $data);
            $connection->commit();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $connection->rollBack();
        }
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
        $connection = $this->getConnection();

        $sumColumn = new \Zend_Db_Expr("SUM(rating_vote.{$connection->quoteIdentifier('percent')})");
        $countColumn = new \Zend_Db_Expr("COUNT(*)");

        $select = $connection->select()->from(
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

        return $connection->fetchAll($select, $bind);
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
        $connection = $this->getConnection();

        $sumColumn = new \Zend_Db_Expr("SUM(rating_vote.{$connection->quoteIdentifier('percent')})");
        $countColumn = new \Zend_Db_Expr('COUNT(*)');
        $select = $connection->select()->from(
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

        $data = $connection->fetchAll($select, [':review_id' => $object->getReviewId()]);

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
        $select = $this->getConnection()->select()->from(
            $this->getTable('rating_entity'),
            ['entity_id']
        )->where(
            'entity_code = :entity_code'
        );

        return $this->getConnection()->fetchOne($select, [':entity_code' => $entityCode]);
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
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), 'rating_id')->where('entity_id = :entity_id');
        $ratingIds = $connection->fetchCol($select, [':entity_id' => $entityId]);

        if ($ratingIds) {
            $where = ['entity_pk_value = ?' => (int)$productId, 'rating_id IN(?)' => $ratingIds];
            $connection->delete($this->getTable('rating_option_vote_aggregated'), $where);
        }

        return $this;
    }
}
