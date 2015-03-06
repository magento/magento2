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
class Logger
{

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
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
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
     * @return $this
     * @throws \Exception
     */
    public function log($customerId, $data)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter */
        $adapter = $this->resource->getConnection('write');
        $data = array_filter($data);
        if (!$data) {
//            throw
        }
        $adapter->insertOnDuplicate(
            $this->mainTableName, array_merge(['customer_id' => $customerId], $data), [array_keys($data)]
        );
        return $this;
    }

    /**
     * Load log by Customer Id.
     *
     * @param int $customerId
     * @return $this
     * @throws \Exception
     */
    public function get($customerId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter */
        $adapter = $this->resource->getConnection('read');

        $select = $adapter->select()
            ->from(
                ['cl' => $this->mainTableName]
            )
            ->joinLeft(
                ['cv' => $this->resource->getTableName($this->visitorTableName)],
                'cv.customer_id = cl.customer_id',
                ['last_visit_at']
            )
            ->where(
                'cl.customer_id = ?', $customerId
            )
            ->order(
                'cv.visitor_id DESC'
            )
            ->limit(1);
        $data = $adapter->fetchRow($select);
        //TODO:: throw exception if empty response
        return $this->logFactory->create([
            'customerId' => $data['customer_id'],
            'lastLoginAt' => $data['last_login_at'],
            'lastLogoutAt' => $data['last_logout_at'],
            'lastVisitAt' => $data['last_visit_at'],
        ]);
    }
}
