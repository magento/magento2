<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            [
                'severity' => 'severity',
                'count_notice' => new \Zend_Db_Expr('COUNT(' . $this->getIdFieldName() . ')')
            ]
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
