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
 * @category    Mage
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Newsletter queue resource model
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Newsletter_Model_Resource_Queue extends Mage_Core_Model_Resource_Db_Abstract
{
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
     * @param Mage_Newsletter_Model_Queue $queue
     * @param array $subscriberIds
     */
    public function addSubscribersToQueue(Mage_Newsletter_Model_Queue $queue, array $subscriberIds)
    {
        if (count($subscriberIds)==0) {
            Mage::throwException(Mage::helper('Mage_Newsletter_Helper_Data')->__('No subscribers selected.'));
        }

        if (!$queue->getId() && $queue->getQueueStatus()!=Mage_Newsletter_Model_Queue::STATUS_NEVER) {
            Mage::throwException(Mage::helper('Mage_Newsletter_Helper_Data')->__('Invalid queue selected.'));
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
        catch (Exception $e) {
            $adapter->rollBack();
        }
    }

    /**
     * Removes subscriber from queue
     *
     * @param Mage_Newsletter_Model_Queue $queue
     */
    public function removeSubscribersFromQueue(Mage_Newsletter_Model_Queue $queue)
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
        catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }
    }

    /**
     * Links queue to store
     *
     * @param Mage_Newsletter_Model_Queue $queue
     * @return Mage_Newsletter_Model_Resource_Queue
     */
    public function setStores(Mage_Newsletter_Model_Queue $queue)
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

        $subscribers = Mage::getResourceSingleton('Mage_Newsletter_Model_Resource_Subscriber_Collection')
            ->addFieldToFilter('store_id', array('in'=>$stores))
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
     * @param Mage_Newsletter_Model_Queue $queue
     * @return array
     */
    public function getStores(Mage_Newsletter_Model_Queue $queue)
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
     * @param Mage_Core_Model_Abstract $queue
     * @return Mage_Newsletter_Model_Resource_Queue
     */
    protected function _afterSave(Mage_Core_Model_Abstract $queue)
    {
        if ($queue->getSaveStoresFlag()) {
            $this->setStores($queue);
        }
        return $this;
    }
}
