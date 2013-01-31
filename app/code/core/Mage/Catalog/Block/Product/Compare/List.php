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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog products compare block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Product_Compare_List extends Mage_Catalog_Block_Product_Compare_Abstract
{
    /**
     * Product Compare items collection
     *
     * @var Mage_Catalog_Model_Resource_Product_Compare_Item_Collection
     */
    protected $_items;

    /**
     * Compare Products comparable attributes cache
     *
     * @var array
     */
    protected $_attributes;

    /**
     * Flag which allow/disallow to use link for as low as price
     *
     * @var bool
     */
    protected $_useLinkForAsLowAs = false;

    /**
     * Customer id
     *
     * @var null|int
     */
    protected $_customerId = null;

    /**
     * Default MAP renderer type
     *
     * @var string
     */
    protected $_mapRenderer = 'msrp_noform';

    /**
     * Retrieve url for adding product to wishlist with params
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getAddToWishlistUrl($product)
    {
        $continueUrl    = Mage::helper('Mage_Core_Helper_Data')->urlEncode($this->getUrl('customer/account'));
        $urlParamName   = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;

        $params = array(
            $urlParamName   => $continueUrl
        );

        return $this->helper('Mage_Wishlist_Helper_Data')->getAddUrlWithParams($product, $params);
    }

    /**
     * Preparing layout
     *
     * @return Mage_Catalog_Block_Product_Compare_List
     */
    protected function _prepareLayout()
    {
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(Mage::helper('Mage_Catalog_Helper_Data')->__('Products Comparison List') . ' - ' . $headBlock->getDefaultTitle());
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrieve Product Compare items collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Compare_Item_Collection
     */
    public function getItems()
    {
        if ($this->_items === null) {
            Mage::helper('Mage_Catalog_Helper_Product_Compare')->setAllowUsedFlat(false);

            $this->_items = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Compare_Item_Collection')
                ->useProductItem(true)
                ->setStoreId(Mage::app()->getStore()->getId());

            if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
                $this->_items->setCustomerId(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId());
            } elseif ($this->_customerId) {
                $this->_items->setCustomerId($this->_customerId);
            } else {
                $this->_items->setVisitorId(Mage::getSingleton('Mage_Log_Model_Visitor')->getId());
            }

            $this->_items
                ->addAttributeToSelect(Mage::getSingleton('Mage_Catalog_Model_Config')->getProductAttributes())
                ->loadComparableAttributes()
                ->addMinimalPrice()
                ->addTaxPercents()
                ->setVisibility(Mage::getSingleton('Mage_Catalog_Model_Product_Visibility')->getVisibleInSiteIds());
        }

        return $this->_items;
    }

    /**
     * Retrieve Product Compare Attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = $this->getItems()->getComparableAttributes();
        }

        return $this->_attributes;
    }

    /**
     * Retrieve Product Attribute Value
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return string
     */
    public function getProductAttributeValue($product, $attribute)
    {
        if (!$product->hasData($attribute->getAttributeCode())) {
            return Mage::helper('Mage_Catalog_Helper_Data')->__('N/A');
        }

        if ($attribute->getSourceModel()
            || in_array($attribute->getFrontendInput(), array('select','boolean','multiselect'))
        ) {
            //$value = $attribute->getSource()->getOptionText($product->getData($attribute->getAttributeCode()));
            $value = $attribute->getFrontend()->getValue($product);
        } else {
            $value = $product->getData($attribute->getAttributeCode());
        }
        return ((string)$value == '') ? Mage::helper('Mage_Catalog_Helper_Data')->__('No') : $value;
    }

    /**
     * Retrieve Print URL
     *
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('*/*/*', array('_current'=>true, 'print'=>1));
    }

    /**
     * Setter for customer id
     *
     * @param int $id
     * @return Mage_Catalog_Block_Product_Compare_List
     */
    public function setCustomerId($id)
    {
        $this->_customerId = $id;
        return $this;
    }
}
