<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

/**
 * Customer log model.
 *
 * @method int getLogId()
 * @method int getCustomerId()
 * @method \Magento\Customer\Model\Log setCustomerId(int $value)
 * @method string getLastVisitAt()
 * @method \Magento\Customer\Model\Log setLastVisitAt(string $value)
 * @method string getLastLoginAt()
 * @method \Magento\Customer\Model\Log setLastLoginAt(string $value)
 * @method string getLastLogoutAt()
 * @method \Magento\Customer\Model\Log setLastLogoutAt(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Log extends \Magento\Framework\Object
{
    /**
     * Name of object id field.
     *
     * @var string
     */
    protected $_idFieldName = 'log_id';

    /**
     * Name of the main data table.
     *
     * @var string
     */
    protected $mainTableName = 'customer_log';

    /**
     * Name of the visitor data table.
     *
     * @var string
     */
    protected $visitorTableName = 'customer_visitor';

    /**
     * Resource instance.
     *
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->resource = $resource;
        $this->dateTime = $dateTime;
    }

    /**
     * Save 'LastLoginAt' date in the customer log.
     *
     * Used in event 'customer_login'.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function saveLastLoginAt(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        $this->setCustomerId($customer->getId());
        $this->setLastLoginAt($this->dateTime->now());

        return $this->save();
    }

    /**
     * Save 'LastLogoutAt' date in the customer log.
     *
     * Used in event 'customer_logout'.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function saveLastLogoutAt(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        $this->setCustomerId($customer->getId());
        $this->setLastLogoutAt($this->dateTime->now());

        return $this->save();
    }

    /**
     * Save (insert new or update existing) log.
     *
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter */
        $adapter = $this->resource->getConnection('write');

        $adapter->insertOnDuplicate(
            $this->mainTableName, $this->_data, array_keys($this->_data)
        );

        $this->setId($adapter->lastInsertId($this->mainTableName));

        return $this;
    }

    /**
     * Load log by Customer Id.
     *
     * @param int $customerId
     * @return $this
     * @throws \Exception
     */
    public function loadByCustomer($customerId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter */
        $adapter = $this->resource->getConnection('read');

        $select = $adapter->select()
            ->from(
                ['cl' => $this->mainTableName]
            )
            ->joinLeft(
                ['cv' => $this->visitorTableName],
                'cv.customer_id = cl.customer_id',
                ['visitor_id', 'last_visit_at']
            )
            ->where(
                'cl.customer_id = ?', $customerId
            )
            ->order(
                'cv.visitor_id DESC'
            )
            ->limit(1);

        $this->setData($adapter->fetchRow($select));

        return $this;
    }
}
