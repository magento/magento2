<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model;

use Magento\ProductAlert\Model\ResourceModel\Stock\Customer\Collection;

/**
 * ProductAlert for back in stock model
 *
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
 * @method int getStoreId()
 * @method \Magento\ProductAlert\Model\Stock setStoreId(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @api
 * @since 100.0.2
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
     * Class constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\ProductAlert\Model\ResourceModel\Stock::class);
    }

    /**
     * Create customer collection.
     *
     * @return Collection
     */
    public function getCustomerCollection()
    {
        return $this->_customerColFactory->create();
    }

    /**
     * Load by param.
     *
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
     * Method for deleting customer from website.
     *
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
