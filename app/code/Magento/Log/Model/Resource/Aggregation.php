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
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->getTable('log_summary'),
            [$adapter->quoteIdentifier('date') => 'MAX(add_date)']
        );

        return $adapter->fetchOne($select);
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
        $adapter = $this->_getReadAdapter();
        $result = ['customers' => 0, 'visitors' => 0];
        $select = $adapter->select()->from(
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

        $customers = $adapter->fetchCol($select);
        $result['customers'] = count($customers);

        $select = $adapter->select();
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

        $result['visitors'] = $adapter->fetchOne($select);

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
        $adapter = $this->_getWriteAdapter();
        if (is_null($id)) {
            $adapter->insert($this->getTable('log_summary'), $data);
        } else {
            $condition = $adapter->quoteInto('summary_id = ?', $id);
            $adapter->update($this->getTable('log_summary'), $data, $condition);
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
        $adapter = $this->_getWriteAdapter();
        $condition = ['add_date < ?' => $date, 'customer_count = 0', 'visitor_count = 0'];
        $adapter->delete($this->getTable('log_summary'), $condition);
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
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->getTable('log_summary'),
            'summary_id'
        )->where(
            'add_date >= ?',
            $from
        )->where(
            'add_date <= ?',
            $to
        );

        return $adapter->fetchOne($select);
    }
}
