<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model\Resource\Visitor;

/**
 * Log Prepare Online visitors resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Online extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\Stdlib\DateTime\DateTime $date)
    {
        $this->_date = $date;
        parent::__construct($resource);
    }

    /**
     * Initialize connection and define resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('log_visitor_online', 'visitor_id');
    }

    /**
     * Prepare online visitors for collection
     *
     * @param \Magento\Log\Model\Visitor\Online $object
     * @return $this
     * @throws \Exception
     */
    public function prepare(\Magento\Log\Model\Visitor\Online $object)
    {
        if ($object->getUpdateFrequency() + $object->getPrepareAt() > time()) {
            return $this;
        }

        $readAdapter = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();

        $writeAdapter->beginTransaction();

        try {
            $writeAdapter->delete($this->getMainTable());

            $visitors = [];
            $lastUrls = [];

            // retrieve online visitors general data

            $lastDate = $this->_date->gmtTimestamp() - $object->getOnlineInterval() * 60;

            $select = $readAdapter->select()->from(
                $this->getTable('log_visitor'),
                ['visitor_id', 'first_visit_at', 'last_visit_at', 'last_url_id']
            )->where(
                'last_visit_at >= ?',
                $readAdapter->formatDate($lastDate)
            );

            $query = $readAdapter->query($select);
            while ($row = $query->fetch()) {
                $visitors[$row['visitor_id']] = $row;
                $lastUrls[$row['last_url_id']] = $row['visitor_id'];
                $visitors[$row['visitor_id']]['visitor_type'] = \Magento\Customer\Model\Visitor::VISITOR_TYPE_VISITOR;
                $visitors[$row['visitor_id']]['customer_id'] = null;
            }

            if (!$visitors) {
                $this->commit();
                return $this;
            }

            // retrieve visitor remote addr
            $select = $readAdapter->select()->from(
                $this->getTable('log_visitor_info'),
                ['visitor_id', 'remote_addr']
            )->where(
                'visitor_id IN(?)',
                array_keys($visitors)
            );

            $query = $readAdapter->query($select);
            while ($row = $query->fetch()) {
                $visitors[$row['visitor_id']]['remote_addr'] = $row['remote_addr'];
            }

            // retrieve visitor last URLs
            $select = $readAdapter->select()->from(
                $this->getTable('log_url_info'),
                ['url_id', 'url']
            )->where(
                'url_id IN(?)',
                array_keys($lastUrls)
            );

            $query = $readAdapter->query($select);
            while ($row = $query->fetch()) {
                $visitorId = $lastUrls[$row['url_id']];
                $visitors[$visitorId]['last_url'] = $row['url'];
            }

            // retrieve customers
            $select = $readAdapter->select()->from(
                $this->getTable('log_customer'),
                ['visitor_id', 'customer_id']
            )->where(
                'visitor_id IN(?)',
                array_keys($visitors)
            );

            $query = $readAdapter->query($select);
            while ($row = $query->fetch()) {
                $visitors[$row['visitor_id']]['visitor_type'] = \Magento\Customer\Model\Visitor::VISITOR_TYPE_CUSTOMER;
                $visitors[$row['visitor_id']]['customer_id'] = $row['customer_id'];
            }

            foreach ($visitors as $visitorData) {
                unset($visitorData['last_url_id']);

                $writeAdapter->insertForce($this->getMainTable(), $visitorData);
            }

            $writeAdapter->commit();
        } catch (\Exception $e) {
            $writeAdapter->rollBack();
            throw $e;
        }

        $object->setPrepareAt();

        return $this;
    }
}
