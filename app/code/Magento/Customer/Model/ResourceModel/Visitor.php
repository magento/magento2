<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\ResourceModel;

/**
 * Class Visitor
 * @package Magento\Customer\Model\ResourceModel
 * @since 2.0.0
 */
class Visitor extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.0.0
     */
    protected $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     * @since 2.0.0
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string $connectionName
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _prepareDataForSave(\Magento\Framework\Model\AbstractModel $visitor)
    {
        return [
            'customer_id' => $visitor->getCustomerId(),
            'session_id' => $visitor->getSessionId(),
            'last_visit_at' => $visitor->getLastVisitAt()
        ];
    }

    /**
     * Clean visitor's outdated records
     *
     * @param \Magento\Customer\Model\Visitor $object
     * @return $this
     * @since 2.0.0
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
}
