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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array('count' => new \Zend_Db_Expr('count(*)'))
        )->where(
            'ip=:ip
                AND  time>=:time
                AND  website_id=:website_id'
        );
        $bind = array('ip' => $ip, 'time' => $startTime, 'website_id' => (int)$websiteId);

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
            array('ip' => $ip, 'time' => $startTime, 'website_id' => $websiteId)
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
