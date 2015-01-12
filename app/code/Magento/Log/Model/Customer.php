<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model;

/**
 * Customer log model
 *
 * @method \Magento\Log\Model\Resource\Customer _getResource()
 * @method \Magento\Log\Model\Resource\Customer getResource()
 * @method int getVisitorId()
 * @method \Magento\Log\Model\Customer setVisitorId(int $value)
 * @method int getCustomerId()
 * @method \Magento\Log\Model\Customer setCustomerId(int $value)
 * @method string getLoginAt()
 * @method \Magento\Log\Model\Customer setLoginAt(string $value)
 * @method string getLogoutAt()
 * @method \Magento\Log\Model\Customer setLogoutAt(string $value)
 * @method int getStoreId()
 * @method \Magento\Log\Model\Customer setStoreId(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Customer extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\Log\Model\Resource\Customer');
    }

    /**
     * Load last log by customer id
     *
     * @param int $customerId
     * @return \Magento\Log\Model\Customer
     */
    public function loadByCustomer($customerId)
    {
        return $this->load($customerId, 'customer_id');
    }

    /**
     * Return last login at in Unix time format
     *
     * @return int
     */
    public function getLoginAtTimestamp()
    {
        $loginAt = $this->getLoginAt();
        if ($loginAt) {
            return $this->dateTime->toTimestamp($loginAt);
        }

        return null;
    }
}
