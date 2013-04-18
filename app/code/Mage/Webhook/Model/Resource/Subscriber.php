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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Resource_Subscriber extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('webhook_subscriber', 'subscriber_id');
    }

    /**
     * Get api user subscribers
     *
     * @param int $apiUserId
     * @return array
     */
    public function getApiUserSubscribers($apiUserId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('subscriber_id'))
            ->where('api_user_id = ?', (int)$apiUserId);
        return $adapter->fetchCol($select);
    }

    /**
     * Gets list ot topics for subscriber
     *
     * @param int $id
     * @return Mage_Webhook_Model_Resource_Subscriber
     */
    protected function _getTopics($id)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getTable('webhook_subscriber_hook'), 'topic')
            ->where('subscriber_id = ?', $id);
        return $adapter->fetchCol($select);
    }

    /**
     * Updates list of topics for subscriber
     *
     * @param array $oldTopics
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Webhook_Model_Resource_Subscriber
     */
    protected function _updateTopics($oldTopics, Mage_Core_Model_Abstract $object)
    {
        $newTopics = $object->getData('topics');
        $availableTopics = $object->getAvailableHooks();
        $id = $object->getId();
        if (!empty($newTopics) && is_array($newTopics)) {
            if (!empty($availableTopics) && is_array($availableTopics)) {
                $newTopics = array_intersect($newTopics, $availableTopics);
            }
            $intersection = array();
            if (!empty($oldTopics) && is_array($oldTopics)) {
                $intersection = array_intersect($newTopics, $oldTopics);
                $oldTopics = array_diff($oldTopics, $intersection);
            } else {
                $oldTopics = array();
            }
            $newTopics = array_diff($newTopics, $intersection);

            $insertData = array();

            foreach ($newTopics as $topic) {
                $insertData[] = array(
                    'subscriber_id' => $id,
                    'topic' => $topic
                );
            }

            if (count($oldTopics) > 0) {
                $this->_getWriteAdapter()->delete(
                    $this->getTable('webhook_subscriber_hook'),
                    array(
                        'subscriber_id = ?' => $id,
                        'topic in (?)' => $oldTopics
                    )
                );
            }

            if (count($insertData) > 0) {
                $this->_getWriteAdapter()->insertMultiple(
                    $this->getTable('webhook_subscriber_hook'),
                    $insertData
                );
            }
        }
        return $this;
    }

    /**
     * Perform actions after object load
     *
     * @param Varien_Object $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object->setData('topics', $this->_getTopics($object->getId()));
        return parent::_afterLoad($object);
    }

    /**
     * Perform actions after object save
     *
     * @param Varien_Object $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldTopics = $this->_getTopics($object->getId());
        $this->_updateTopics($oldTopics, $object);
        return parent::_afterSave($object);
    }

    /**
     * Returns the list of callback topics which already have subscribers
     *
     * @param array $topicsList
     * @return array
     */
    public function getSubscribedCallbackTopics($topicsList)
    {
        $select = $this->_getReadAdapter()->select();
        $select->from($this->getMainTable() . ' as main_table', 'hook.topic as topic')->joinInner(
            array('hook' => $this->getTable('webhook_subscriber_hook')),
            'main_table.subscriber_id = hook.subscriber_id',
            'hook.topic as topic'
        )->where('hook.topic in(?)', $topicsList)
        ->where($this->_getReadAdapter()->prepareSqlCondition(
            'main_table.api_user_id',
            array('notnull' => true)
        ));

        $result = $this->_getReadAdapter()->fetchCol($select);

        return $result;
    }
}
