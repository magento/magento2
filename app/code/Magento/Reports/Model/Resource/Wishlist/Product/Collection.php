<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist Report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Wishlist\Product;

class Collection extends \Magento\Wishlist\Model\Resource\Item\Collection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Magento\Wishlist\Model\Wishlist', 'Magento\Wishlist\Model\Resource\Wishlist');
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
            array('wi' => $wishlistItemTable),
            'wi.product_id = e.entity_id',
            array('wishlists' => new \Zend_Db_Expr('COUNT(wi.wishlist_item_id)'))
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
            array('wishlist' => $this->getTable('wishlist')),
            array('wishlist_cnt' => new \Zend_Db_Expr('COUNT(wishlist.wishlist_id)'), 'wishlist.customer_id')
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
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::GROUP);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);
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
