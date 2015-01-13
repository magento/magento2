<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Resource\Address;

/**
 * Customers collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Eav\Model\Entity\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Customer\Model\Address', 'Magento\Customer\Model\Resource\Address');
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
