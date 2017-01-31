<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist model collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Model\ResourceModel\Wishlist;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Wishlist\Model\Wishlist', 'Magento\Wishlist\Model\ResourceModel\Wishlist');
    }

    /**
     * Filter collection by customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function filterByCustomerId($customerId)
    {
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }

    /**
     * Filter collection by customer ids
     *
     * @param array $customerIds
     * @return $this
     */
    public function filterByCustomerIds(array $customerIds)
    {
        $this->addFieldToFilter('customer_id', ['in' => $customerIds]);
        return $this;
    }
}
