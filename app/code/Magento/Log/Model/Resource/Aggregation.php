<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model\Resource;

/**
 * Log aggregation resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Aggregation extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('log_summary', 'log_summary_id');
    }

    /**
     * Retrieve last added record
     *
     * @return string
     */
    public function getLastRecordDate()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('log_summary'),
            [$connection->quoteIdentifier('date') => 'MAX(add_date)']
        );

        return $connection->fetchOne($select);
    }

    /**
     * Retrieve count of visitors, customers
     *
     * @param string $from
     * @param string $to
     * @param int $store
     * @return array
     */
    public function getCounts($from, $to, $store)
    {
        $connection = $this->getConnection();
        $result = ['customers' => 0, 'visitors' => 0];
        $select = $connection->select()->from(
            $this->getTable('log_customer'),
            'visitor_id'
        )->where(
            'login_at >= ?',
            $from
        )->where(
            'login_at <= ?',
            $to
        );
        if ($store) {
            $select->where('store_id = ?', $store);
        }

        $customers = $connection->fetchCol($select);
        $result['customers'] = count($customers);

        $select = $connection->select();
        $select->from(
            $this->getTable('log_visitor'),
            'COUNT(*)'
        )->where(
            'first_visit_at >= ?',
            $from
        )->where(
            'first_visit_at <= ?',
            $to
        );

        if ($store) {
            $select->where('store_id = ?', $store);
        }
        if ($result['customers']) {
            $select->where('visitor_id NOT IN(?)', $customers);
        }

        $result['visitors'] = $connection->fetchOne($select);

        return $result;
    }

    /**
     * Save log
     *
     * @param array $data
     * @param int $id
     * @return void
     */
    public function saveLog($data, $id = null)
    {
        $connection = $this->getConnection();
        if ($id === null) {
            $connection->insert($this->getTable('log_summary'), $data);
        } else {
            $condition = $connection->quoteInto('summary_id = ?', $id);
            $connection->update($this->getTable('log_summary'), $data, $condition);
        }
    }

    /**
     * Remove empty records
     *
     * @param string $date
     * @return void
     */
    public function removeEmpty($date)
    {
        $connection = $this->getConnection();
        $condition = ['add_date < ?' => $date, 'customer_count = 0', 'visitor_count = 0'];
        $connection->delete($this->getTable('log_summary'), $condition);
    }

    /**
     * Retrieve log id
     *
     * @param string $from
     * @param string $to
     * @return string
     */
    public function getLogId($from, $to)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('log_summary'),
            'summary_id'
        )->where(
            'add_date >= ?',
            $from
        )->where(
            'add_date <= ?',
            $to
        );

        return $connection->fetchOne($select);
    }
}
