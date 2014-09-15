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

            $visitors = array();
            $lastUrls = array();

            // retrieve online visitors general data

            $lastDate = $this->_date->gmtTimestamp() - $object->getOnlineInterval() * 60;

            $select = $readAdapter->select()->from(
                $this->getTable('log_visitor'),
                array('visitor_id', 'first_visit_at', 'last_visit_at', 'last_url_id')
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
                array('visitor_id', 'remote_addr')
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
                array('url_id', 'url')
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
                array('visitor_id', 'customer_id')
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
