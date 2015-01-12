<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model\Resource;

/**
 * Log Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Log extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->_date = $date;
        $this->_eventManager = $eventManager;
        $this->dateTime = $dateTime;
        parent::__construct($resource);
    }

    /**
     * Init Resource model and connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('log_visitor', 'visitor_id');
    }

    /**
     * Clean logs
     *
     * @param \Magento\Log\Model\Log $object
     * @return $this
     */
    public function clean(\Magento\Log\Model\Log $object)
    {
        $cleanTime = $object->getLogCleanTime();

        $this->_cleanVisitors($cleanTime);
        $this->_cleanCustomers($cleanTime);
        $this->_cleanUrls();

        return $this;
    }

    /**
     * Clean visitors table
     *
     * @param int $time
     * @return $this
     */
    protected function _cleanVisitors($time)
    {
        $readAdapter = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();

        $timeLimit = $this->dateTime->formatDate($this->_date->gmtTimestamp() - $time);

        while (true) {
            $select = $readAdapter->select()->from(
                ['visitor_table' => $this->getTable('log_visitor')],
                ['visitor_id' => 'visitor_table.visitor_id']
            )->joinLeft(
                ['customer_table' => $this->getTable('log_customer')],
                'visitor_table.visitor_id = customer_table.visitor_id AND customer_table.log_id IS NULL',
                []
            )->where(
                'visitor_table.last_visit_at < ?',
                $timeLimit
            )->limit(
                100
            );

            $visitorIds = $readAdapter->fetchCol($select);

            if (!$visitorIds) {
                break;
            }

            $condition = ['visitor_id IN (?)' => $visitorIds];

            // remove visitors from log/quote
            $writeAdapter->delete($this->getTable('log_quote'), $condition);

            // remove visitors from log/url
            $writeAdapter->delete($this->getTable('log_url'), $condition);

            // remove visitors from log/visitor_info
            $writeAdapter->delete($this->getTable('log_visitor_info'), $condition);

            // remove visitors from log/visitor
            $writeAdapter->delete($this->getTable('log_visitor'), $condition);
        }

        return $this;
    }

    /**
     * Clean customer table
     *
     * @param int $time
     * @return $this
     */
    protected function _cleanCustomers($time)
    {
        $readAdapter = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();

        $timeLimit = $this->dateTime->formatDate($this->_date->gmtTimestamp() - $time);

        // retrieve last active customer log id
        $lastLogId = $readAdapter->fetchOne(
            $readAdapter->select()->from(
                $this->getTable('log_customer'),
                'log_id'
            )->where(
                'login_at < ?',
                $timeLimit
            )->order(
                'log_id DESC'
            )->limit(
                1
            )
        );

        if (!$lastLogId) {
            return $this;
        }

        // Order by desc log_id before grouping (within-group aggregates query pattern)
        $select = $readAdapter->select()->from(
            ['log_customer_main' => $this->getTable('log_customer')],
            ['log_id']
        )->joinLeft(
            ['log_customer' => $this->getTable('log_customer')],
            'log_customer_main.customer_id = log_customer.customer_id ' .
            'AND log_customer_main.log_id < log_customer.log_id',
            []
        )->where(
            'log_customer.customer_id IS NULL'
        )->where(
            'log_customer_main.log_id < ?',
            $lastLogId + 1
        );

        $needLogIds = [];
        $query = $readAdapter->query($select);
        while ($row = $query->fetch()) {
            $needLogIds[$row['log_id']] = 1;
        }

        $customerLogId = 0;
        while (true) {
            $visitorIds = [];
            $select = $readAdapter->select()->from(
                $this->getTable('log_customer'),
                ['log_id', 'visitor_id']
            )->where(
                'log_id > ?',
                $customerLogId
            )->where(
                'log_id < ?',
                $lastLogId + 1
            )->order(
                'log_id'
            )->limit(
                100
            );

            $query = $readAdapter->query($select);
            $count = 0;
            while ($row = $query->fetch()) {
                $count++;
                $customerLogId = $row['log_id'];
                if (!isset($needLogIds[$row['log_id']])) {
                    $visitorIds[] = $row['visitor_id'];
                }
            }

            if (!$count) {
                break;
            }

            if ($visitorIds) {
                $condition = ['visitor_id IN (?)' => $visitorIds];

                // remove visitors from log/quote
                $writeAdapter->delete($this->getTable('log_quote'), $condition);

                // remove visitors from log/url
                $writeAdapter->delete($this->getTable('log_url'), $condition);

                // remove visitors from log/visitor_info
                $writeAdapter->delete($this->getTable('log_visitor_info'), $condition);

                // remove visitors from log/visitor
                $writeAdapter->delete($this->getTable('log_visitor'), $condition);

                // remove customers from log/customer
                $writeAdapter->delete($this->getTable('log_customer'), $condition);
            }

            if ($customerLogId == $lastLogId) {
                break;
            }
        }

        return $this;
    }

    /**
     * Clean url table
     *
     * @return $this
     */
    protected function _cleanUrls()
    {
        $readAdapter = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();

        while (true) {
            $select = $readAdapter->select()->from(
                ['url_info_table' => $this->getTable('log_url_info')],
                ['url_id']
            )->joinLeft(
                ['url_table' => $this->getTable('log_url')],
                'url_info_table.url_id = url_table.url_id',
                []
            )->where(
                'url_table.url_id IS NULL'
            )->limit(
                100
            );

            $urlIds = $readAdapter->fetchCol($select);

            if (!$urlIds) {
                break;
            }

            $writeAdapter->delete($this->getTable('log_url_info'), ['url_id IN (?)' => $urlIds]);
        }

        return $this;
    }
}
