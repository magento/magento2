<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Newsletter\Model\Queue as ModelQueue;

/**
 * Newsletter queue resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @api
 * @since 100.0.2
 */
class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Subscriber collection
     *
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection
     */
    protected $_subscriberCollection;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection $subscriberCollection
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection $subscriberCollection,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_subscriberCollection = $subscriberCollection;
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('newsletter_queue', 'queue_id');
    }

    /**
     * Add subscribers to queue
     *
     * @param ModelQueue $queue
     * @param array $subscriberIds
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addSubscribersToQueue(ModelQueue $queue, array $subscriberIds)
    {
        if (count($subscriberIds) == 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('There are no subscribers selected.'));
        }

        if (!$queue->getId() && $queue->getQueueStatus() != \Magento\Newsletter\Model\Queue::STATUS_NEVER) {
            throw new \Magento\Framework\Exception\LocalizedException(__('You selected an invalid queue.'));
        }

        $connection = $this->getConnection();

        $select = $connection->select();
        $select->from(
            $this->getTable('newsletter_queue_link'),
            'subscriber_id'
        )->where(
            'queue_id = ?',
            $queue->getId()
        )->where(
            'subscriber_id in (?)',
            $subscriberIds
        );

        $usedIds = array_flip($connection->fetchCol($select));
        $subscriberIds = array_flip($subscriberIds);
        $newIds = array_diff_key($subscriberIds, $usedIds);

        $connection->beginTransaction();
        try {
            foreach (array_keys($newIds) as $subscriberId) {
                $data = [];
                $data['queue_id'] = $queue->getId();
                $data['subscriber_id'] = $subscriberId;
                $connection->insert($this->getTable('newsletter_queue_link'), $data);
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
        }
    }

    /**
     * Removes subscriber from queue
     *
     * @param ModelQueue $queue
     * @return void
     * @throws \Exception
     */
    public function removeSubscribersFromQueue(ModelQueue $queue)
    {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $connection->delete(
                $this->getTable('newsletter_queue_link'),
                ['queue_id = ?' => $queue->getId(), 'letter_sent_at IS NULL']
            );

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * Links queue to store
     *
     * @param ModelQueue $queue
     * @return $this
     */
    public function setStores(ModelQueue $queue)
    {
        $connection = $this->getConnection();
        $connection->delete($this->getTable('newsletter_queue_store_link'), ['queue_id = ?' => $queue->getId()]);

        $stores = $queue->getStores();
        if (!is_array($stores)) {
            $stores = [];
        }

        foreach ($stores as $storeId) {
            $data = [];
            $data['store_id'] = $storeId;
            $data['queue_id'] = $queue->getId();
            $connection->insert($this->getTable('newsletter_queue_store_link'), $data);
        }
        $this->removeSubscribersFromQueue($queue);

        if (count($stores) == 0) {
            return $this;
        }

        $subscribers = $this->_subscriberCollection->addFieldToFilter(
            'store_id',
            ['in' => $stores]
        )->useOnlySubscribed()->load();

        $subscriberIds = [];

        foreach ($subscribers as $subscriber) {
            $subscriberIds[] = $subscriber->getId();
        }

        if (count($subscriberIds) > 0) {
            $this->addSubscribersToQueue($queue, $subscriberIds);
        }

        return $this;
    }

    /**
     * Returns queue linked stores
     *
     * @param ModelQueue $queue
     * @return array
     */
    public function getStores(ModelQueue $queue)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('newsletter_queue_store_link'),
            'store_id'
        )->where(
            'queue_id = :queue_id'
        );

        if (!($result = $connection->fetchCol($select, ['queue_id' => $queue->getId()]))) {
            $result = [];
        }

        return $result;
    }

    /**
     * Saving template after saving queue action
     *
     * @param \Magento\Framework\Model\AbstractModel $queue
     * @return $this
     */
    protected function _afterSave(AbstractModel $queue)
    {
        if ($queue->getSaveStoresFlag()) {
            $this->setStores($queue);
        }
        return $this;
    }
}
