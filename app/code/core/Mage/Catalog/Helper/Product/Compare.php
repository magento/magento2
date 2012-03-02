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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Product Compare Helper
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Helper_Product_Compare extends Mage_Core_Helper_Url
{
    /**
     * Product Compare Items Collection
     *
     * @var Mage_Catalog_Model_Resource_Product_Compare_Item_Collection
     */
    protected $_itemCollection;

    /**
     * Product Comapare Items Collection has items flag
     *
     * @var bool
     */
    protected $_hasItems;

    /**
     * Allow used Flat catalog product for product compare items collection
     *
     * @var bool
     */
    protected $_allowUsedFlat = true;

    /**
     * Customer id
     *
     * @var null|int
     */
    protected $_customerId = null;

    /**
     * Retrieve Catalog Session instance
     *
     * @return Mage_Catalog_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('Mage_Catalog_Model_Session');
    }

    /**
     * Retrieve compare list url
     *
     * @return string
     */
    public function getListUrl()
    {
         $itemIds = array();
         foreach ($this->getItemCollection() as $item) {
             $itemIds[] = $item->getId();
         }

         $params = array(
            'items'=>implode(',', $itemIds),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
         );

         return $this->_getUrl('catalog/product_compare', $params);
    }

    /**
     * Get parameters used for build add product to compare list urls
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  array
     */
    protected function _getUrlParams($product)
    {
        return array(
            'product' => $product->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
        );
    }

    /**
     * Retrieve url for adding product to conpare list
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  string
     */
    public function getAddUrl($product)
    {
        return $this->_getUrl('catalog/product_compare/add', $this->_getUrlParams($product));
    }

    /**
     * Retrive add to wishlist url
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getAddToWishlistUrl($product)
    {
        $beforeCompareUrl = Mage::getSingleton('Mage_Catalog_Model_Session')->getBeforeCompareUrl();

        $params = array(
            'product'=>$product->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl($beforeCompareUrl)
        );

        return $this->_getUrl('wishlist/index/add', $params);
    }

    /**
     * Retrive add to cart url
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getAddToCartUrl($product)
    {
        $beforeCompareUrl = Mage::getSingleton('Mage_Catalog_Model_Session')->getBeforeCompareUrl();
        $params = array(
            'product'=>$product->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl($beforeCompareUrl)
        );

        return $this->_getUrl('checkout/cart/add', $params);
    }

    /**
     * Retrieve remove item from compare list url
     *
     * @param   $item
     * @return  string
     */
    public function getRemoveUrl($item)
    {
        $params = array(
            'product'=>$item->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
        );
        return $this->_getUrl('catalog/product_compare/remove', $params);
    }

    /**
     * Retrieve clear compare list url
     *
     * @return string
     */
    public function getClearListUrl()
    {
        $params = array(
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
        );
        return $this->_getUrl('catalog/product_compare/clear', $params);
    }

    /**
     * Retrieve compare list items collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Compare_Item_Collection
     */
    public function getItemCollection()
    {
        if (!$this->_itemCollection) {
            $this->_itemCollection = Mage::getResourceModel(
                    'Mage_Catalog_Model_Resource_Product_Compare_Item_Collection'
                )
                ->useProductItem(true)
                ->setStoreId(Mage::app()->getStore()->getId());

            if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
                $this->_itemCollection->setCustomerId(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId());
            } elseif ($this->_customerId) {
                $this->_itemCollection->setCustomerId($this->_customerId);
            } else {
                $this->_itemCollection->setVisitorId(Mage::getSingleton('Mage_Log_Model_Visitor')->getId());
            }

            $this->_itemCollection->setVisibility(
                Mage::getSingleton('Mage_Catalog_Model_Product_Visibility')->getVisibleInSiteIds()
            );

            /* Price data is added to consider item stock status using price index */
            $this->_itemCollection->addPriceData();

            $this->_itemCollection->addAttributeToSelect('name')
                ->addUrlRewrite()
                ->load();

            /* update compare items count */
            $this->_getSession()->setCatalogCompareItemsCount(count($this->_itemCollection));
        }

        return $this->_itemCollection;
    }

    /**
     * Calculate cache product compare collection
     *
     * @param  bool $logout
     * @return Mage_Catalog_Helper_Product_Compare
     */
    public function calculate($logout = false)
    {
        // first visit
        if (!$this->_getSession()->hasCatalogCompareItemsCount() && !$this->_customerId) {
            $count = 0;
        } else {
            /** @var $collection Mage_Catalog_Model_Resource_Product_Compare_Item_Collection */
            $collection = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Compare_Item_Collection')
                ->useProductItem(true);
            if (!$logout && Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
                $collection->setCustomerId(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId());
            } elseif ($this->_customerId) {
                $collection->setCustomerId($this->_customerId);
            } else {
                $collection->setVisitorId(Mage::getSingleton('Mage_Log_Model_Visitor')->getId());
            }

            /* Price data is added to consider item stock status using price index */
            $collection->addPriceData()
                ->setVisibility(Mage::getSingleton('Mage_Catalog_Model_Product_Visibility')->getVisibleInSiteIds());

            $count = $collection->getSize();
        }

        $this->_getSession()->setCatalogCompareItemsCount($count);

        return $this;
    }

    /**
     * Retrieve count of items in compare list
     *
     * @return int
     */
    public function getItemCount()
    {
        if (!$this->_getSession()->hasCatalogCompareItemsCount()) {
            $this->calculate();
        }

        return $this->_getSession()->getCatalogCompareItemsCount();
    }

    /**
     * Check has items
     *
     * @return bool
     */
    public function hasItems()
    {
        return $this->getItemCount() > 0;
    }

    /**
     * Set is allow used flat (for collection)
     *
     * @param bool $flag
     * @return Mage_Catalog_Helper_Product_Compare
     */
    public function setAllowUsedFlat($flag)
    {
        $this->_allowUsedFlat = (bool)$flag;
        return $this;
    }

    /**
     * Retrieve is allow used flat (for collection)
     *
     * @return bool
     */
    public function getAllowUsedFlat()
    {
        return $this->_allowUsedFlat;
    }

    /**
     * Setter for customer id
     *
     * @param int $id
     * @return Mage_Catalog_Helper_Product_Compare
     */
    public function setCustomerId($id)
    {
        $this->_customerId = $id;
        return $this;
    }
}
