<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel;

/**
 * Class Visitor
 * @package Magento\Customer\Model\ResourceModel
 */
class Visitor extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $connectionName = null
    ) {
        $this->date = $date;
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('customer_visitor', 'visitor_id');
    }

    /**
     * Prepare data for save
     *
     * @param \Magento\Framework\Model\AbstractModel $visitor
     * @return array
     */
    protected function _prepareDataForSave(\Magento\Framework\Model\AbstractModel $visitor)
    {
        return [
            'customer_id' => $visitor->getCustomerId(),
            'last_visit_at' => $visitor->getLastVisitAt()
        ];
    }

    /**
     * Clean visitor's outdated records
     *
     * @param \Magento\Customer\Model\Visitor $object
     * @return $this
     */
    public function clean(\Magento\Customer\Model\Visitor $object)
    {
        $cleanTime = $object->getCleanTime();
        $connection = $this->getConnection();
        $timeLimit = $this->dateTime->formatDate($this->date->gmtTimestamp() - $cleanTime);
        while (true) {
            $select = $connection->select()->from(
                ['visitor_table' => $this->getTable('customer_visitor')],
                ['visitor_id' => 'visitor_table.visitor_id']
            )->where(
                'visitor_table.last_visit_at < ?',
                $timeLimit
            )->limit(
                100
            );
            $visitorIds = $connection->fetchCol($select);
            if (!$visitorIds) {
                break;
            }
            $condition = ['visitor_id IN (?)' => $visitorIds];
            $connection->delete($this->getTable('customer_visitor'), $condition);
        }

        return $this;
    }

    /**
     * Gets created at value for the visitor id.
     *
     * @param int $visitorId
     * @return int|null
     */
    public function fetchCreatedAt(int $visitorId): ?int
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['visitor_table' => $this->getTable('customer_visitor')],
            ['created_at' => 'visitor_table.created_at']
        )->where(
            'visitor_table.visitor_id = ?',
            $visitorId,
            \Zend_Db::BIGINT_TYPE
        )->limit(
            1
        );
        $lookup = $connection->fetchRow($select);
        if (empty($lookup) || $lookup['created_at'] == null) {
            return null;
        }
        return strtotime($lookup['created_at']);
    }

    /**
     * Gets created at value for the visitor id by customer id
     *
     * @param int $customerId
     * @return int|null
     */
    public function fetchCreatedAtByCustomer(int $customerId): ?int
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['visitor_table' => $this->getTable('customer_visitor')],
            ['created_at' => 'visitor_table.created_at']
        )->where(
            'visitor_table.customer_id = ?',
            $customerId,
            \Zend_Db::INT_TYPE
        )->order(
            'visitor_table.visitor_id DESC'
        )->limit(
            1
        );
        $lookup = $connection->fetchRow($select);
        if (empty($lookup) || $lookup['created_at'] == null) {
            return null;
        }
        return strtotime($lookup['created_at']);
    }

    /**
     * Gets created at value for the visitor id by customer id
     *
     * @param int $customerId
     * @return int|null
     */
    public function fetchLastVisitAtByCustomer(int $customerId): ?int
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['visitor_table' => $this->getTable('customer_visitor')],
            ['last_visit_at' => 'visitor_table.last_visit_at']
        )->where(
            'visitor_table.customer_id = ?',
            $customerId,
            \Zend_Db::INT_TYPE
        )->order(
            'visitor_table.visitor_id DESC'
        )->limit(
            1
        );
        $lookup = $connection->fetchRow($select);
        if (empty($lookup) || $lookup['last_visit_at'] == null) {
            return null;
        }
        return strtotime($lookup['last_visit_at']);
    }

    /**
     * Update visitor session created at column value
     *
     * @param int $visitorId
     * @param int $timestamp
     * @return void
     */
    public function updateCreatedAt(int $visitorId, int $timestamp): void
    {
        $this->getConnection()->update(
            $this->getTable('customer_visitor'),
            ['created_at' => $this->dateTime->formatDate($timestamp)],
            $this->getConnection()->quoteInto('visitor_id = ?', $visitorId)
        );
    }

    /**
     * Update visitor session visitor id column value
     *
     * @param int $visitorId
     * @param int $customerId
     * @return void
     */
    public function updateCustomerId(int $visitorId, int $customerId): void
    {
        $this->getConnection()->update(
            $this->getTable('customer_visitor'),
            ['customer_id' => $customerId],
            $this->getConnection()->quoteInto('visitor_id = ?', $visitorId)
        );
    }
}
