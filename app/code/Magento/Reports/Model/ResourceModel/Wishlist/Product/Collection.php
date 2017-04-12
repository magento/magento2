<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist Report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Wishlist\Product;

class Collection extends \Magento\Wishlist\Model\ResourceModel\Item\Collection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Magento\Wishlist\Model\Wishlist::class, \Magento\Wishlist\Model\ResourceModel\Wishlist::class);
    }

    /**
     * Add wishlist count
     *
     * @return $this
     */
    public function addWishlistCount()
    {
        $wishlistItemTable = $this->getTable('wishlist_item');
        $this->getSelect()->join(
            ['wi' => $wishlistItemTable],
            'wi.product_id = e.entity_id',
            ['wishlists' => new \Zend_Db_Expr('COUNT(wi.wishlist_item_id)')]
        )->where(
            'wi.product_id = e.entity_id'
        )->group(
            'wi.product_id'
        );

        $this->getEntity()->setStore(0);
        return $this;
    }

    /**
     * Add customer count to result
     *
     * @return $this
     */
    public function getCustomerCount()
    {
        $this->getSelect()->reset();

        $this->getSelect()->from(
            ['wishlist' => $this->getTable('wishlist')],
            ['wishlist_cnt' => new \Zend_Db_Expr('COUNT(wishlist.wishlist_id)'), 'wishlist.customer_id']
        )->group(
            'wishlist.customer_id'
        );
        return $this;
    }

    /**
     * Get select count sql
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $countSelect->columns("COUNT(*)");

        return $countSelect;
    }

    /**
     * Set order to result
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_DESC)
    {
        if ($attribute == 'wishlists') {
            $this->getSelect()->order($attribute . ' ' . $dir);
        } else {
            parent::setOrder($attribute, $dir);
        }

        return $this;
    }
}
