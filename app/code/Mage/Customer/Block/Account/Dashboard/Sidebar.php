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
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Account dashboard sidebar
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Customer_Block_Account_Dashboard_Sidebar extends Mage_Core_Block_Template
{
    protected $_cartItemsCount;

    /**
     * Enter description here...
     *
     * @var Mage_Wishlist_Model_Wishlist
     */
    protected $_wishlist;

    protected $_compareItems;

    public function getShoppingCartUrl()
    {
        return Mage::getUrl('checkout/cart');
    }

    public function getCartItemsCount()
    {
        if( !$this->_cartItemsCount ) {
            $this->_cartItemsCount = Mage::getModel('Mage_Sales_Model_Quote')
                ->setId(Mage::getSingleton('Mage_Checkout_Model_Session')->getQuote()->getId())
                ->getItemsCollection()
                ->getSize();
        }

        return $this->_cartItemsCount;
    }

    public function getWishlist()
    {
        if( !$this->_wishlist ) {
            $this->_wishlist = Mage::getModel('Mage_Wishlist_Model_Wishlist')
                ->loadByCustomer(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer());
            $this->_wishlist->getItemCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('small_image')
                ->addAttributeToFilter('store_id', array('in' => $this->_wishlist->getSharedStoreIds()))
                ->addAttributeToSort('added_at', 'desc')
                ->setCurPage(1)
                ->setPageSize(3)
                ->load();
        }

        return $this->_wishlist->getItemCollection();
    }

    public function getWishlistCount()
    {
        return $this->getWishlist()->getSize();
    }

    public function getWishlistAddToCartLink($wishlistItem)
    {
        return Mage::getUrl('wishlist/index/cart', array('item' => $wishlistItem->getId()));
    }

    public function getCompareItems()
    {
        if( !$this->_compareItems ) {
            $this->_compareItems =
                Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Compare_Item_Collection')
                    ->setStoreId(Mage::app()->getStore()->getId());
            $this->_compareItems->setCustomerId(
                Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId()
            );
            $this->_compareItems
                ->addAttributeToSelect('name')
                ->useProductItem()
                ->load();
        }
        return $this->_compareItems;
    }

     public function getCompareJsObjectName()
     {
         return "dashboardSidebarCompareJsObject";
     }

     public function getCompareRemoveUrlTemplate()
     {
         return $this->getUrl('catalog/product_compare/remove',array('product'=>'#{id}'));
     }

     public function getCompareAddUrlTemplate()
     {
         return $this->getUrl('catalog/product_compare/add',array('product'=>'#{id}'));
     }

     public function getCompareUrl()
     {
         return $this->getUrl('catalog/product_compare');
     }
}
