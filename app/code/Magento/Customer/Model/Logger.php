<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

/**
 * Customer log data logger.
 *
 * Saves and retrieves customer log data.
 */
class Logger
{
    /**
     * Resource instance.
     *
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * @var \Magento\Customer\Model\LogFactory
     */
    protected $logFactory;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Customer\Model\LogFactory $logFactory
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Customer\Model\LogFactory $logFactory
    ) {
        $this->resource = $resource;
        $this->logFactory = $logFactory;
    }

    /**
     * Save (insert new or update existing) log.
     *
     * @param int $customerId
     * @param array $data
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function log($customerId, array $data)
    {
        $data = array_filter($data);

        if (!$data) {
            throw new \InvalidArgumentException("Log data is empty");
        }

        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter */
        $adapter = $this->resource->getConnection('write');

        $adapter->insertOnDuplicate(
            $this->resource->getTableName('customer_log'),
            array_merge(['customer_id' => $customerId], $data),
            array_keys($data)
        );

        return $this;
    }

    /**
     * Load log by Customer Id.
     *
     * @param int $customerId
     * @return Log
     */
    public function get($customerId = null)
    {
        $data = (null !== $customerId) ? $this->loadLogData($customerId) : [];

        return $this->logFactory->create(
            [
                'customerId' => isset($data['customer_id']) ? $data['customer_id'] : null,
                'lastLoginAt' => isset($data['last_login_at']) ? $data['last_login_at'] : null,
                'lastLogoutAt' => isset($data['last_logout_at']) ? $data['last_logout_at'] : null,
                'lastVisitAt' => isset($data['last_visit_at']) ? $data['last_visit_at'] : null
            ]
        );
    }

    /**
     * Load customer log data by customer id
     *
     * @param int $customerId
     * @return array
     */
    protected function loadLogData($customerId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter */
        $adapter = $this->resource->getConnection('read');

        $select = $adapter->select()
            ->from(
                ['cl' => $this->resource->getTableName('customer_log')]
            )
            ->joinLeft(
                ['cv' => $this->resource->getTableName('customer_visitor')],
                'cv.customer_id = cl.customer_id',
                ['last_visit_at']
            )
            ->where(
                'cl.customer_id = ?',
                $customerId
            )
            ->order(
                'cv.visitor_id DESC'
            )
            ->limit(1);

        return $adapter->fetchRow($select);
    }
}
