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
 * @package     Mage_Poll
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Poll resource model
 *
 * @category    Mage
 * @package     Mage_Poll
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Poll_Model_Resource_Poll extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('poll', 'poll_id');
    }

    /**
     * Initialize unique fields
     *
     * @return Mage_Poll_Model_Resource_Poll
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array(
            'field' => 'poll_title',
            'title' => Mage::helper('Mage_Poll_Helper_Data')->__('Poll with the same question')
        ));
        return $this;
    }

    /**
     * Get select object for not closed poll ids
     *
     * @param Mage_Poll_Model_Poll $object
     * @return
     */
    protected function _getSelectIds($object)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('main_table'=>$this->getMainTable()), $this->getIdFieldName())
            ->where('closed = ?', 0);

        $excludeIds = $object->getExcludeFilter();
        if ($excludeIds) {
            $select->where('main_table.poll_id NOT IN(?)', $excludeIds);
        }

        $storeId = $object->getStoreFilter();
        if ($storeId) {
            $select->join(
                array('store' => $this->getTable('poll_store')),
                'main_table.poll_id=store.poll_id AND store.store_id = ' . $read->quote($storeId),
                array()
            );
        }

        return $select;
    }

    /**
     * Get random identifier not closed poll
     *
     * @param Mage_Poll_Model_Poll $object
     * @return int
     */
    public function getRandomId($object)
    {
        $select = $this->_getSelectIds($object)->orderRand()->limit(1);
        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Get all ids for not closed polls
     *
     * @param Mage_Poll_Model_Poll $object
     * @return array
     */
    public function getAllIds($object)
    {
        $select = $this->_getSelectIds($object);
        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Check answer id existing for poll
     *
     * @param Mage_Poll_Model_Poll $poll
     * @param int $answerId
     * @return bool
     */
    public function checkAnswerId($poll, $answerId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('poll_answer'), 'answer_id')
            ->where('poll_id = :poll_id')
            ->where('answer_id = :answer_id');
        $bind = array(':poll_id' => $poll->getId(), ':answer_id' => $answerId);
        return $this->_getReadAdapter()->fetchOne($select, $bind);
    }

    /**
     * Get voted poll ids by specified IP-address
     * Will return non-empty only if appropriate option in config is enabled
     * If poll id is not empty, it will look only for records with specified value
     *
     * @param string $ipAddress
     * @param int $pollId
     * @return array
     */
    public function getVotedPollIdsByIp($ipAddress, $pollId = false)
    {
        // check if validation by ip is enabled
        if (!Mage::getModel('Mage_Poll_Model_Poll')->isValidationByIp()) {
            return array();
        }

        // look for ids in database
        $select = $this->_getReadAdapter()->select()
            ->distinct()
            ->from($this->getTable('poll_vote'), 'poll_id')
            ->where('ip_address = :ip_address');
        $bind = array(':ip_address' => ip2long($ipAddress));
        if (!empty($pollId)) {
            $select->where('poll_id = :poll_id');
            $bind[':poll_id'] = $pollId;
        }
        $result = $this->_getReadAdapter()->fetchCol($select, $bind);
        if (empty($result)) {
            $result = array();
        }
        return $result;
    }

    /**
     * Resett votes count
     *
     * @param Mage_Poll_Model_Poll $object
     * @return Mage_Poll_Model_Poll
     */
    public function resetVotesCount($object)
    {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->from($this->getTable('poll_answer'), new Zend_Db_Expr("SUM(votes_count)"))
            ->where('poll_id = ?', $object->getPollId());
        $adapter->update(
            $this->getMainTable(),
            array('votes_count' => new Zend_Db_Expr("($select)")),
            array('poll_id = ' . $adapter->quote($object->getPollId()))
        );
        return $object;
    }

    /**
     * Load store Ids array
     *
     * @param Mage_Poll_Model_Poll $object
     */
    public function loadStoreIds(Mage_Poll_Model_Poll $object)
    {
        $pollId   = $object->getId();
        $storeIds = array();
        if ($pollId) {
            $storeIds = $this->lookupStoreIds($pollId);
        }
        $object->setStoreIds($storeIds);
    }

    /**
     * Delete current poll from the table poll_store and then
     * insert to update "poll to store" relations
     *
     * @param Mage_Core_Model_Abstract $object
     */
    public function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /** stores */
        $deleteWhere = $this->_getWriteAdapter()->quoteInto('poll_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('poll_store'), $deleteWhere);

        foreach ($object->getStoreIds() as $storeId) {
            $pollStoreData = array(
            'poll_id'   => $object->getId(),
            'store_id'  => $storeId
            );
            $this->_getWriteAdapter()->insert($this->getTable('poll_store'), $pollStoreData);
        }

        /** answers */
        foreach ($object->getAnswers() as $answer) {
            $answer->setPollId($object->getId());
            $answer->save();
        }
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        return $this->_getReadAdapter()->fetchCol(
            $this->_getReadAdapter()->select()
                ->from($this->getTable('poll_store'), 'store_id')
                ->where("{$this->getIdFieldName()} = :id_field"),
            array(':id_field' => $id)
        );
    }
}
