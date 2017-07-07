<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Address;

/**
 * Customers collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Eav\Model\Entity\Collection\VersionControl\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Customer\Model\Address::class, \Magento\Customer\Model\ResourceModel\Address::class);
    }

    /**
     * Set customer filter
     *
     * @param \Magento\Customer\Model\Customer|array $customer
     * @return $this
     */
    public function setCustomerFilter($customer)
    {
        if (is_array($customer)) {
            $this->addAttributeToFilter('parent_id', ['in' => $customer]);
        } elseif ($customer->getId()) {
            $this->addAttributeToFilter('parent_id', $customer->getId());
        } else {
            $this->addAttributeToFilter('parent_id', '-1');
        }
        return $this;
    }
}
