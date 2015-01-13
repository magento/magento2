<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model;

use Magento\ProductAlert\Model\Resource\Price\Customer\Collection;

/**
 * ProductAlert for changed price model
 *
 * @method \Magento\ProductAlert\Model\Resource\Price _getResource()
 * @method \Magento\ProductAlert\Model\Resource\Price getResource()
 * @method int getCustomerId()
 * @method \Magento\ProductAlert\Model\Price setCustomerId(int $value)
 * @method int getProductId()
 * @method \Magento\ProductAlert\Model\Price setProductId(int $value)
 * @method float getPrice()
 * @method \Magento\ProductAlert\Model\Price setPrice(float $value)
 * @method int getWebsiteId()
 * @method \Magento\ProductAlert\Model\Price setWebsiteId(int $value)
 * @method string getAddDate()
 * @method \Magento\ProductAlert\Model\Price setAddDate(string $value)
 * @method string getLastSendDate()
 * @method \Magento\ProductAlert\Model\Price setLastSendDate(string $value)
 * @method int getSendCount()
 * @method \Magento\ProductAlert\Model\Price setSendCount(int $value)
 * @method int getStatus()
 * @method \Magento\ProductAlert\Model\Price setStatus(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Price extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\ProductAlert\Model\Resource\Price\Customer\CollectionFactory
     */
    protected $_customerColFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\ProductAlert\Model\Resource\Price\Customer\CollectionFactory $customerColFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\ProductAlert\Model\Resource\Price\Customer\CollectionFactory $customerColFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
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
        $this->_init('Magento\ProductAlert\Model\Resource\Price');
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
        if (!is_null($this->getProductId()) && !is_null($this->getCustomerId()) && !is_null($this->getWebsiteId())) {
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
