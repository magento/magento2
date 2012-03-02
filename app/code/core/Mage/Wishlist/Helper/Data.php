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
 * @package     Mage_Wishlist
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist Data Helper
 *
 * @category   Mage
 * @package    Mage_Wishlist
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Wishlist_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Config key 'Display Wishlist Summary'
     */
    const XML_PATH_WISHLIST_LINK_USE_QTY = 'wishlist/wishlist_link/use_qty';

    /**
     * Config key 'Display Out of Stock Products'
     */
    const XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK = 'cataloginventory/options/show_out_of_stock';

    /**
     * Customer Wishlist instance
     *
     * @var Mage_Wishlist_Model_Wishlist
     */
    protected $_wishlist = null;

    /**
     * Wishlist Product Items Collection
     *
     * @var Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected $_productCollection = null;

    /**
     * Wishlist Items Collection
     *
     * @var Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected $_wishlistItemCollection = null;

    /**
     * Retreive customer session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('Mage_Customer_Model_Session');
    }

    /**
     * Retrieve customer login status
     *
     * @return bool
     */
    protected function _isCustomerLogIn()
    {
        return $this->_getCustomerSession()->isLoggedIn();
    }

    /**
     * Retrieve logged in customer
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCurrentCustomer()
    {
        return $this->_getCustomerSession()->getCustomer();
    }

    /**
     * Retrieve wishlist by logged in customer
     *
     * @return Mage_Wishlist_Model_Wishlist
     */
    public function getWishlist()
    {
        if (is_null($this->_wishlist)) {
            if (Mage::registry('shared_wishlist')) {
                $this->_wishlist = Mage::registry('shared_wishlist');
            }
            elseif (Mage::registry('wishlist')) {
                $this->_wishlist = Mage::registry('wishlist');
            }
            else {
                $this->_wishlist = Mage::getModel('Mage_Wishlist_Model_Wishlist');
                if ($this->_getCustomerSession()->isLoggedIn()) {
                    $this->_wishlist->loadByCustomer($this->_getCustomerSession()->getCustomer());
                }
            }
        }
        return $this->_wishlist;
    }

    /**
     * Retrieve wishlist item count (include config settings)
     * Used in top link menu only
     *
     * @return int
     */
    public function getItemCount()
    {
        $storedDisplayType = $this->_getCustomerSession()->getWishlistDisplayType();
        $currentDisplayType = Mage::getStoreConfig(self::XML_PATH_WISHLIST_LINK_USE_QTY);

        $storedDisplayOutOfStockProducts = $this->_getCustomerSession()->getDisplayOutOfStockProducts();
        $currentDisplayOutOfStockProducts = Mage::getStoreConfig(self::XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK);
        if (!$this->_getCustomerSession()->hasWishlistItemCount()
                || ($currentDisplayType != $storedDisplayType)
                || $this->_getCustomerSession()->hasDisplayOutOfStockProducts()
                || ($currentDisplayOutOfStockProducts != $storedDisplayOutOfStockProducts)) {
            $this->calculate();
        }

        return $this->_getCustomerSession()->getWishlistItemCount();
    }

    /**
     * Retrieve wishlist items collection
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function getWishlistItemCollection()
    {
        if (is_null($this->_wishlistItemCollection)) {
            $this->_wishlistItemCollection = $this->getWishlist()
                ->getItemCollection();
        }
        return $this->_wishlistItemCollection;
    }

    /**
     * Retrieve Item Store for URL
     *
     * @param Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     * @return Mage_Core_Model_Store
     */
    protected function _getUrlStore($item)
    {
        $storeId = null;
        $product = null;
        if ($item instanceof Mage_Wishlist_Model_Item) {
            $product = $item->getProduct();
        } elseif ($item instanceof Mage_Catalog_Model_Product) {
            $product = $item;
        }
        if ($product) {
            if ($product->isVisibleInSiteVisibility()) {
                $storeId = $product->getStoreId();
            }
            else if ($product->hasUrlDataObject()) {
                $storeId = $product->getUrlDataObject()->getStoreId();
            }
        }
        return Mage::app()->getStore($storeId);
    }

    /**
     * Retrieve URL for removing item from wishlist
     *
     * @param Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     * @return string
     */
    public function getRemoveUrl($item)
    {
        return $this->_getUrl('wishlist/index/remove',
            array('item' => $item->getWishlistItemId())
        );
    }

    /**
     * Retrieve URL for removing item from wishlist
     *
     * @param Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     * @return string
     */
    public function getConfigureUrl($item)
    {
        return $this->_getUrl('wishlist/index/configure', array(
            'item' => $item->getWishlistItemId()
        ));
    }

    /**
     * Retrieve url for adding product to wishlist
     *
     * @param Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     *
     * @return  string|bool
     */
    public function getAddUrl($item)
    {
        return $this->getAddUrlWithParams($item);
    }

    /**
     * Retrieve url for updating product in wishlist
     *
     * @param Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     *
     * @return  string|bool
     */
    public function getUpdateUrl($item)
    {
        $itemId = null;
        if ($item instanceof Mage_Catalog_Model_Product) {
            $itemId = $item->getWishlistItemId();
        }
        if ($item instanceof Mage_Wishlist_Model_Item) {
            $itemId = $item->getId();
        }

        if ($itemId) {
            $params['id'] = $itemId;
            return $this->_getUrlStore($item)->getUrl('wishlist/index/updateItemOptions', $params);
        }

        return false;
    }

    /**
     * Retrieve url for adding product to wishlist with params
     *
     * @param Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     * @param array $params
     *
     * @return  string|bool
     */
    public function getAddUrlWithParams($item, array $params = array())
    {
        $productId = null;
        if ($item instanceof Mage_Catalog_Model_Product) {
            $productId = $item->getEntityId();
        }
        if ($item instanceof Mage_Wishlist_Model_Item) {
            $productId = $item->getProductId();
        }

        if ($productId) {
            $params['product'] = $productId;
            return $this->_getUrlStore($item)->getUrl('wishlist/index/add', $params);
        }

        return false;
    }

    /**
     * Retrieve URL for adding item to shoping cart
     *
     * @param string|Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     * @return  string
     */
    public function getAddToCartUrl($item)
    {
        $urlParamName = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $continueUrl  = Mage::helper('Mage_Core_Helper_Data')->urlEncode(
            Mage::getUrl('*/*/*', array(
                '_current'      => true,
                '_use_rewrite'  => true,
                '_store_to_url' => true,
            ))
        );

        $urlParamName = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $params = array(
            'item' => is_string($item) ? $item : $item->getWishlistItemId(),
            $urlParamName => $continueUrl
        );
        return $this->_getUrlStore($item)->getUrl('wishlist/index/cart', $params);
    }

    /**
     * Retrieve URL for adding item to shoping cart from shared wishlist
     *
     * @param string|Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     * @return  string
     */
    public function getSharedAddToCartUrl($item)
    {
        $continueUrl  = Mage::helper('Mage_Core_Helper_Data')->urlEncode(Mage::getUrl('*/*/*', array(
            '_current'      => true,
            '_use_rewrite'  => true,
            '_store_to_url' => true,
        )));

        $urlParamName = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $params = array(
            'item' => is_string($item) ? $item : $item->getWishlistItemId(),
            $urlParamName => $continueUrl
        );
        return $this->_getUrlStore($item)->getUrl('wishlist/shared/cart', $params);
    }

    /**
     * Retrieve customer wishlist url
     *
     * @return string
     */
    public function getListUrl()
    {
        return $this->_getUrl('wishlist');
    }

    /**
     * Check is allow wishlist module
     *
     * @return bool
     */
    public function isAllow()
    {
        if ($this->isModuleOutputEnabled() && Mage::getStoreConfig('wishlist/general/active')) {
            return true;
        }
        return false;
    }

    /**
     * Check is allow wishlist action in shopping cart
     *
     * @return bool
     */
    public function isAllowInCart()
    {
        return $this->isAllow() && $this->_isCustomerLogIn();
    }

    /**
     * Retrieve customer name
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->_getCurrentCustomer()->getName();
    }

    /**
     * Retrieve RSS URL
     *
     * @return string
     */
    public function getRssUrl()
    {
        $customer = $this->_getCurrentCustomer();
        $key = $customer->getId().','.$customer->getEmail();
        return $this->_getUrl(
            'rss/index/wishlist',
            array(
                'data' => Mage::helper('Mage_Core_Helper_Data')->urlEncode($key),
                '_secure' => false
            )
        );
    }

    /**
     * Is allow RSS
     *
     * @return bool
     */
    public function isRssAllow()
    {
        return Mage::getStoreConfigFlag('rss/wishlist/active');
    }

    /**
     * Retrieve default empty comment message
     *
     * @return string
     */
    public function defaultCommentString()
    {
        return $this->__('Please, enter your comments...');
    }

    /**
     * Calculate count of wishlist items and put value to customer session.
     * Method called after wishlist modifications and trigger 'wishlist_items_renewed' event.
     * Depends from configuration.
     *
     * @return Mage_Wishlist_Helper_Data
     */
    public function calculate()
    {
        $session = $this->_getCustomerSession();
        $count = 0;
        if ($this->_isCustomerLogIn()) {
            $collection = $this->getWishlistItemCollection()->setInStockFilter(true);
            if (Mage::getStoreConfig(self::XML_PATH_WISHLIST_LINK_USE_QTY)) {
                $count = $collection->getItemsQty();
            } else {
                $count = $collection->getSize();
            }
            $session->setWishlistDisplayType(Mage::getStoreConfig(self::XML_PATH_WISHLIST_LINK_USE_QTY));
            $session->setDisplayOutOfStockProducts(
                Mage::getStoreConfig(self::XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK)
            );
        }
        $session->setWishlistItemCount($count);
        Mage::dispatchEvent('wishlist_items_renewed');
        return $this;
    }
}
