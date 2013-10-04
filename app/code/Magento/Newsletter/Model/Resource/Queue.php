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
 * @category    Magento
 * @package     Magento_Newsletter
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Newsletter queue resource model
 *
 * @category    Magento
 * @package     Magento_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Model\Resource;

class Queue extends \Magento\Core\Model\Resource\Db\AbstractDb
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
     * @param \Magento\Core\Model\Resource $resource
     * @param \Magento\Newsletter\Model\Resource\Subscriber\Collection $subscriberCollection
     */
    public function __construct(
        \Magento\Core\Model\Resource $resource,
        \Magento\Newsletter\Model\Resource\Subscriber\Collection $subscriberCollection
    ) {
        parent::__construct($resource);
        $this->_subscriberCollection = $subscriberCollection;
    }

    /**
     * Define main table
     *
     */
    protected function _construct()
    {
        $this->_init('newsletter_queue', 'queue_id');
    }

    /**
     * Add subscribers to queue
     *
     * @param \Magento\Newsletter\Model\Queue $queue
     * @param array $subscriberIds
     * @throws \Magento\Core\Exception
     */
    public function addSubscribersToQueue(\Magento\Newsletter\Model\Queue $queue, array $subscriberIds)
    {
        if (count($subscriberIds)==0) {
            throw new \Magento\Core\Exception(__('There are no subscribers selected.'));
        }

        if (!$queue->getId() && $queue->getQueueStatus()!=Magento_Newsletter_Model_Queue::STATUS_NEVER) {
            throw new \Magento\Core\Exception(__('You selected an invalid queue.'));
        }

        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select();
        $select->from($this->getTable('newsletter_queue_link'), 'subscriber_id')
            ->where('queue_id = ?', $queue->getId())
            ->where('subscriber_id in (?)', $subscriberIds);

        $usedIds = $adapter->fetchCol($select);
        $adapter->beginTransaction();
        try {
            foreach ($subscriberIds as $subscriberId) {
                if (in_array($subscriberId, $usedIds)) {
                    continue;
                }
                $data = array();
                $data['queue_id'] = $queue->getId();
                $data['subscriber_id'] = $subscriberId;
                $adapter->insert($this->getTable('newsletter_queue_link'), $data);
            }
            $adapter->commit();
        }
        catch (\Exception $e) {
            $adapter->rollBack();
        }
    }

    /**
     * Removes subscriber from queue
     *
     * @param \Magento\Newsletter\Model\Queue $queue
     */
    public function removeSubscribersFromQueue(\Magento\Newsletter\Model\Queue $queue)
    {
        $adapter = $this->_getWriteAdapter();
        try {
            $adapter->beginTransaction();
            $adapter->delete(
                $this->getTable('newsletter_queue_link'),
                array(
                    'queue_id = ?' => $queue->getId(),
                    'letter_sent_at IS NULL'
                )
            );

            $adapter->commit();
        }
        catch (\Exception $e) {
            $adapter->rollBack();
            throw $e;
        }
    }

    /**
     * Links queue to store
     *
     * @param \Magento\Newsletter\Model\Queue $queue
     * @return \Magento\Newsletter\Model\Resource\Queue
     */
    public function setStores(\Magento\Newsletter\Model\Queue $queue)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->delete(
            $this->getTable('newsletter_queue_store_link'),
            array('queue_id = ?' => $queue->getId())
        );

        $stores = $queue->getStores();
        if (!is_array($stores)) {
            $stores = array();
        }

        foreach ($stores as $storeId) {
            $data = array();
            $data['store_id'] = $storeId;
            $data['queue_id'] = $queue->getId();
            $adapter->insert($this->getTable('newsletter_queue_store_link'), $data);
        }
        $this->removeSubscribersFromQueue($queue);

        if (count($stores) == 0) {
            return $this;
        }

        $subscribers = $this->_subscriberCollection->addFieldToFilter('store_id', array('in'=>$stores))
            ->useOnlySubscribed()
            ->load();

        $subscriberIds = array();

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
     * @param \Magento\Newsletter\Model\Queue $queue
     * @return array
     */
    public function getStores(\Magento\Newsletter\Model\Queue $queue)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from($this->getTable('newsletter_queue_store_link'), 'store_id')
            ->where('queue_id = :queue_id');

        if (!($result = $adapter->fetchCol($select, array('queue_id'=>$queue->getId())))) {
            $result = array();
        }

        return $result;
    }

    /**
     * Saving template after saving queue action
     *
     * @param \Magento\Core\Model\AbstractModel $queue
     * @return \Magento\Newsletter\Model\Resource\Queue
     */
    protected function _afterSave(\Magento\Core\Model\AbstractModel $queue)
    {
        if ($queue->getSaveStoresFlag()) {
            $this->setStores($queue);
        }
        return $this;
    }
}
