<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist model collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Model\ResourceModel\Wishlist;

/**
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Wishlist\Model\Wishlist::class, \Magento\Wishlist\Model\ResourceModel\Wishlist::class);
    }

    /**
     * Filter collection by customer id
     *
     * @param int $customerId
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function filterByCustomerIds(array $customerIds)
    {
        $this->addFieldToFilter('customer_id', ['in' => $customerIds]);
        return $this;
    }
}
