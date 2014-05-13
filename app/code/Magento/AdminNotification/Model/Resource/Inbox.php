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
namespace Magento\AdminNotification\Model\Resource;

/**
 * AdminNotification Inbox model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Inbox extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * AdminNotification Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('adminnotification_inbox', 'notification_id');
    }

    /**
     * Load latest notice
     *
     * @param \Magento\AdminNotification\Model\Inbox $object
     * @return $this
     */
    public function loadLatestNotice(\Magento\AdminNotification\Model\Inbox $object)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->getMainTable()
        )->order(
            $this->getIdFieldName() . ' DESC'
        )->where(
            'is_read != 1'
        )->where(
            'is_remove != 1'
        )->limit(
            1
        );
        $data = $adapter->fetchRow($select);

        if ($data) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Get notifications grouped by severity
     *
     * @param \Magento\AdminNotification\Model\Inbox $object
     * @return array
     */
    public function getNoticeStatus(\Magento\AdminNotification\Model\Inbox $object)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->getMainTable(),
            array(
                'severity' => 'severity',
                'count_notice' => new \Zend_Db_Expr('COUNT(' . $this->getIdFieldName() . ')')
            )
        )->group(
            'severity'
        )->where(
            'is_remove=?',
            0
        )->where(
            'is_read=?',
            0
        );
        $return = $adapter->fetchPairs($select);
        return $return;
    }

    /**
     * Save notifications (if not exists)
     *
     * @param \Magento\AdminNotification\Model\Inbox $object
     * @param array $data
     * @return void
     */
    public function parse(\Magento\AdminNotification\Model\Inbox $object, array $data)
    {
        $adapter = $this->_getWriteAdapter();
        foreach ($data as $item) {
            $select = $adapter->select()->from($this->getMainTable())->where('title = ?', $item['title']);

            if (empty($item['url'])) {
                $select->where('url IS NULL');
            } else {
                $select->where('url = ?', $item['url']);
            }

            if (isset($item['internal'])) {
                $row = false;
                unset($item['internal']);
            } else {
                $row = $adapter->fetchRow($select);
            }

            if (!$row) {
                $adapter->insert($this->getMainTable(), $item);
            }
        }
    }
}
