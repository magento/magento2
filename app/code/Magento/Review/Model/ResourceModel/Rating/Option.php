<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\ResourceModel\Rating;

/**
 * Rating option resource model
 *
 * @api
 * @since 100.0.2
 */
class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Review table
     *
     * @var string
     */
    protected $_reviewTable;

    /**
     * Rating option table
     *
     * @var string
     */
    protected $_ratingOptionTable;

    /**
     * Rating vote table
     *
     * @var string
     */
    protected $_ratingVoteTable;

    /**
     * Aggregate table
     *
     * @var string
     */
    protected $_aggregateTable;

    /**
     * Review store table
     *
     * @var string
     */
    protected $_reviewStoreTable;

    /**
     * Rating store table
     *
     * @var string
     */
    protected $_ratingStoreTable;

    /**
     * Option data
     *
     * @var array
     */
    protected $_optionData;

    /**
     * Option id
     *
     * @var int
     */
    protected $_optionId;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Review\Model\Rating\Option\VoteFactory
     */
    protected $_ratingOptionVoteF;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $_remoteAddress;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Review\Model\Rating\Option\VoteFactory $ratingOptionVoteF
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Review\Model\Rating\Option\VoteFactory $ratingOptionVoteF,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        $connectionName = null
    ) {
        $this->_customerSession = $customerSession;
        $this->_ratingOptionVoteF = $ratingOptionVoteF;
        $this->_remoteAddress = $remoteAddress;
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization. Define other tables name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rating_option', 'option_id');

        $this->_reviewTable = $this->getTable('review');
        $this->_ratingOptionTable = $this->getTable('rating_option');
        $this->_ratingVoteTable = $this->getTable('rating_option_vote');
        $this->_aggregateTable = $this->getTable('rating_option_vote_aggregated');
        $this->_reviewStoreTable = $this->getTable('review_store');
        $this->_ratingStoreTable = $this->getTable('rating_store');
    }

    /**
     * Add vote
     *
     * @param \Magento\Review\Model\Rating\Option $option
     * @return $this
     */
    public function addVote($option)
    {
        $connection = $this->getConnection();
        $optionData = $this->loadDataById($option->getId());
        $data = [
            'option_id' => $option->getId(),
            'review_id' => $option->getReviewId(),
            'percent' => $optionData['value'] / 5 * 100,
            'value' => $optionData['value'],
        ];

        if (!$option->getDoUpdate()) {
            $data['remote_ip'] = $this->_remoteAddress->getRemoteAddress();
            $data['remote_ip_long'] = $this->_remoteAddress->getRemoteAddress(true);
            $data['customer_id'] = $this->_customerSession->getCustomerId();
            $data['entity_pk_value'] = $option->getEntityPkValue();
            $data['rating_id'] = $option->getRatingId();
        }

        $connection->beginTransaction();
        try {
            if ($option->getDoUpdate()) {
                $condition = ['vote_id = ?' => $option->getVoteId(), 'review_id = ?' => $option->getReviewId()];
                $connection->update($this->_ratingVoteTable, $data, $condition);
                $this->aggregate($option);
            } else {
                $connection->insert($this->_ratingVoteTable, $data);
                $option->setVoteId($connection->lastInsertId($this->_ratingVoteTable));
                $this->aggregate($option);
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new \Exception($e->getMessage());
        }
        return $this;
    }

    /**
     * Aggregate options
     *
     * @param \Magento\Review\Model\Rating\Option $option
     * @return void
     */
    public function aggregate($option)
    {
        $vote = $this->_ratingOptionVoteF->create()->load($option->getVoteId());
        $this->aggregateEntityByRatingId($vote->getRatingId(), $vote->getEntityPkValue());
    }

    /**
     * Aggregate entity by rating id
     *
     * @param int $ratingId
     * @param int $entityPkValue
     * @return void
     */
    public function aggregateEntityByRatingId($ratingId, $entityPkValue)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->_aggregateTable,
            ['store_id', 'primary_id']
        )->where(
            'rating_id = :rating_id'
        )->where(
            'entity_pk_value = :pk_value'
        );
        $bind = [':rating_id' => $ratingId, ':pk_value' => $entityPkValue];
        $oldData = $connection->fetchPairs($select, $bind);

        $appVoteCountCond = $connection->getCheckSql('review.status_id=1', 'vote.vote_id', 'NULL');
        $appVoteValueSumCond = $connection->getCheckSql('review.status_id=1', 'vote.value', '0');

        $select = $connection->select()->from(
            ['vote' => $this->_ratingVoteTable],
            [
                'vote_count' => new \Zend_Db_Expr('COUNT(vote.vote_id)'),
                'vote_value_sum' => new \Zend_Db_Expr('SUM(vote.value)'),
                'app_vote_count' => new \Zend_Db_Expr("COUNT({$appVoteCountCond})"),
                'app_vote_value_sum' => new \Zend_Db_Expr("SUM({$appVoteValueSumCond})")
            ]
        )->join(
            ['review' => $this->_reviewTable],
            'vote.review_id=review.review_id',
            []
        )->joinLeft(
            ['store' => $this->_reviewStoreTable],
            'vote.review_id=store.review_id',
            'store_id'
        )->join(
            ['rstore' => $this->_ratingStoreTable],
            'vote.rating_id=rstore.rating_id AND rstore.store_id=store.store_id',
            []
        )->where(
            'vote.rating_id = :rating_id'
        )->where(
            'vote.entity_pk_value = :pk_value'
        )->group(
            ['vote.rating_id', 'vote.entity_pk_value', 'store.store_id']
        );

        $perStoreInfo = $connection->fetchAll($select, $bind);

        $usedStores = [];
        foreach ($perStoreInfo as $row) {
            $saveData = [
                'rating_id' => $ratingId,
                'entity_pk_value' => $entityPkValue,
                'vote_count' => $row['vote_count'],
                'vote_value_sum' => $row['vote_value_sum'],
                'percent' => $row['vote_value_sum'] / $row['vote_count'] / 5 * 100,
                'percent_approved' => $row['app_vote_count'] ? $row['app_vote_value_sum'] /
                $row['app_vote_count'] /
                5 *
                100 : 0,
                'store_id' => $row['store_id'],
            ];

            if (isset($oldData[$row['store_id']])) {
                $condition = ['primary_id = ?' => $oldData[$row['store_id']]];
                $connection->update($this->_aggregateTable, $saveData, $condition);
            } else {
                $connection->insert($this->_aggregateTable, $saveData);
            }

            $usedStores[] = $row['store_id'];
        }

        $toDelete = array_diff(array_keys($oldData), $usedStores);

        foreach ($toDelete as $storeId) {
            $condition = ['primary_id = ?' => $oldData[$storeId]];
            $connection->delete($this->_aggregateTable, $condition);
        }
    }

    /**
     * Load object data by optionId
     * Method renamed from 'load'.
     *
     * @param int $optionId
     * @return array
     */
    public function loadDataById($optionId)
    {
        if (!$this->_optionData || $this->_optionId != $optionId) {
            $connection = $this->getConnection();
            $select = $connection->select();
            $select->from($this->_ratingOptionTable)->where('option_id = :option_id');

            $data = $connection->fetchRow($select, [':option_id' => $optionId]);

            $this->_optionData = $data;
            $this->_optionId = $optionId;
            return $data;
        }

        return $this->_optionData;
    }
}
