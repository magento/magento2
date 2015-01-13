<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sendfriend\Model\Resource;

/**
 * SendFriend Log Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Sendfriend extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sendfriend_log', 'log_id');
    }

    /**
     * Retrieve Sended Emails By Ip
     *
     * @param \Magento\Sendfriend\Model\Sendfriend $object
     * @param int $ip
     * @param int $startTime
     * @param int $websiteId
     * @return int
     */
    public function getSendCount($object, $ip, $startTime, $websiteId = null)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->getMainTable(),
            ['count' => new \Zend_Db_Expr('count(*)')]
        )->where(
            'ip=:ip
                AND  time>=:time
                AND  website_id=:website_id'
        );
        $bind = ['ip' => $ip, 'time' => $startTime, 'website_id' => (int)$websiteId];

        $row = $adapter->fetchRow($select, $bind);
        return $row['count'];
    }

    /**
     * Add sended email by ip item
     *
     * @param int $ip
     * @param int $startTime
     * @param int $websiteId
     * @return $this
     */
    public function addSendItem($ip, $startTime, $websiteId)
    {
        $this->_getWriteAdapter()->insert(
            $this->getMainTable(),
            ['ip' => $ip, 'time' => $startTime, 'website_id' => $websiteId]
        );
        return $this;
    }

    /**
     * Delete Old logs
     *
     * @param int $time
     * @return $this
     */
    public function deleteLogsBefore($time)
    {
        $cond = $this->_getWriteAdapter()->quoteInto('time<?', $time);
        $this->_getWriteAdapter()->delete($this->getMainTable(), $cond);

        return $this;
    }
}
