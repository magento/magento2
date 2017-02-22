<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model;

use Magento\ProductAlert\Model\ResourceModel\Stock\Customer\Collection;

/**
 * ProductAlert for back in stock model
 *
 * @method \Magento\ProductAlert\Model\ResourceModel\Stock _getResource()
 * @method \Magento\ProductAlert\Model\ResourceModel\Stock getResource()
 * @method int getCustomerId()
 * @method \Magento\ProductAlert\Model\Stock setCustomerId(int $value)
 * @method int getProductId()
 * @method \Magento\ProductAlert\Model\Stock setProductId(int $value)
 * @method int getWebsiteId()
 * @method \Magento\ProductAlert\Model\Stock setWebsiteId(int $value)
 * @method string getAddDate()
 * @method \Magento\ProductAlert\Model\Stock setAddDate(string $value)
 * @method string getSendDate()
 * @method \Magento\ProductAlert\Model\Stock setSendDate(string $value)
 * @method int getSendCount()
 * @method \Magento\ProductAlert\Model\Stock setSendCount(int $value)
 * @method int getStatus()
 * @method \Magento\ProductAlert\Model\Stock setStatus(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Stock extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\ProductAlert\Model\ResourceModel\Stock\Customer\CollectionFactory
     */
    protected $_customerColFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\ProductAlert\Model\ResourceModel\Stock\Customer\CollectionFactory $customerColFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\ProductAlert\Model\ResourceModel\Stock\Customer\CollectionFactory $customerColFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_customerColFactory = $customerColFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\ProductAlert\Model\ResourceModel\Stock');
    }

    /**
     * @return Collection
     */
    public function getCustomerCollection()
    {
        return $this->_customerColFactory->create();
    }

    /**
     * @return $this
     */
    public function loadByParam()
    {
        if ($this->getProductId() !== null && $this->getCustomerId() !== null && $this->getWebsiteId() !== null) {
            $this->getResource()->loadByParam($this);
        }
        return $this;
    }

    /**
     * @param int $customerId
     * @param int $websiteId
     * @return $this
     */
    public function deleteCustomer($customerId, $websiteId = 0)
    {
        $this->getResource()->deleteCustomer($this, $customerId, $websiteId);
        return $this;
    }
}
