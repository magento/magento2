<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\Resource;

use Magento\Framework\Model\AbstractModel;
use Magento\Newsletter\Model\Queue as ModelQueue;

/**
 * Newsletter queue resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Queue extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Subscriber collection
     *
     * @var \Magento\Newsletter\Model\Resource\Subscriber\Collection
     */
    protected $_subscriberCollection;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Newsletter\Model\Resource\Subscriber\Collection $subscriberCollection
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Newsletter\Model\Resource\Subscriber\Collection $subscriberCollection
    ) {
        parent::__construct($resource);
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
     * @throws \Magento\Framework\Model\Exception
     */
    public function addSubscribersToQueue(ModelQueue $queue, array $subscriberIds)
    {
        if (count($subscriberIds) == 0) {
            throw new \Magento\Framework\Model\Exception(__('There are no subscribers selected.'));
        }

        if (!$queue->getId() && $queue->getQueueStatus() != \Magento\Newsletter\Model\Queue::STATUS_NEVER) {
            throw new \Magento\Framework\Model\Exception(__('You selected an invalid queue.'));
        }

        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select();
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

        $usedIds = $adapter->fetchCol($select);
        $adapter->beginTransaction();
        try {
            foreach ($subscriberIds as $subscriberId) {
                if (in_array($subscriberId, $usedIds)) {
                    continue;
                }
                $data = [];
                $data['queue_id'] = $queue->getId();
                $data['subscriber_id'] = $subscriberId;
                $adapter->insert($this->getTable('newsletter_queue_link'), $data);
            }
            $adapter->commit();
        } catch (\Exception $e) {
            $adapter->rollBack();
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
        $adapter = $this->_getWriteAdapter();
        try {
            $adapter->beginTransaction();
            $adapter->delete(
                $this->getTable('newsletter_queue_link'),
                ['queue_id = ?' => $queue->getId(), 'letter_sent_at IS NULL']
            );

            $adapter->commit();
        } catch (\Exception $e) {
            $adapter->rollBack();
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
        $adapter = $this->_getWriteAdapter();
        $adapter->delete($this->getTable('newsletter_queue_store_link'), ['queue_id = ?' => $queue->getId()]);

        $stores = $queue->getStores();
        if (!is_array($stores)) {
            $stores = [];
        }

        foreach ($stores as $storeId) {
            $data = [];
            $data['store_id'] = $storeId;
            $data['queue_id'] = $queue->getId();
            $adapter->insert($this->getTable('newsletter_queue_store_link'), $data);
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
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->getTable('newsletter_queue_store_link'),
            'store_id'
        )->where(
            'queue_id = :queue_id'
        );

        if (!($result = $adapter->fetchCol($select, ['queue_id' => $queue->getId()]))) {
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
