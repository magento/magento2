<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\ResourceModel;

use Magento\AdminNotification\Model\Inbox as InboxModel;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Zend_Db_Expr;

/**
 * Inbox resource model
 *
 * @package Magento\AdminNotification\Model\ResourceModel
 * @api
 * @since 100.0.2
 */
class Inbox extends AbstractDb
{
    /**
     * AdminNotification Resource initialization
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void //phpcs:ignore
    {
        $this->_init('adminnotification_inbox', 'notification_id');
    }

    /**
     * Load latest notice
     *
     * @param InboxModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadLatestNotice(InboxModel $object)
    {
        $connection = $this->getConnection();
        if ($connection instanceof AdapterInterface) {
            $select = $connection->select()->from(
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
            $data = $connection->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }

            $this->_afterLoad($object);
        }
        return $this;
    }

    /**
     * Get notifications grouped by severity
     *
     * @param InboxModel $object
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getNoticeStatus(InboxModel $object): ?array
    {
        $connection = $this->getConnection();
        if ($connection instanceof AdapterInterface) {
            $select = $connection->select()->from(
                $this->getMainTable(),
                [
                    'severity' => 'severity',
                    'count_notice' => new Zend_Db_Expr('COUNT(' . $this->getIdFieldName() . ')')
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
            return $connection->fetchPairs($select);
        }
        return [];
    }

    /**
     * Save notifications (if not exists)
     *
     * @param InboxModel $object
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function parse(InboxModel $object, array $data)
    {
        $connection = $this->getConnection();
        if ($connection instanceof AdapterInterface) {
            foreach ($data as $item) {
                $select = $connection->select()->from($this->getMainTable())->where('title = ?', $item['title']);

                if (empty($item['url'])) {
                    $select->where('url IS NULL');
                } else {
                    $select->where('url = ?', $item['url']);
                }

                if (isset($item['internal'])) {
                    $row = false;
                    unset($item['internal']);
                } else {
                    $row = $connection->fetchRow($select);
                }

                if (!$row) {
                    $connection->insert($this->getMainTable(), $item);
                }
            }
        }
    }
}
