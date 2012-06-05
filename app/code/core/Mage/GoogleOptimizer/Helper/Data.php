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
 * @package     Mage_GoogleOptimizer
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Optimizer Data Helper
 *
 * @category   Mage
 * @package    Mage_GoogleOptimizer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleOptimizer_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED = 'google/optimizer/active';
    const XML_PATH_ALLOWED_ATTRIBUTES = 'admin/attributes';

    const MAX_ATTRIBUTE_LENGTH_LIMIT = 25;

    protected $_storeId = null;

    protected $_activeForCmsFlag = null;

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    public function getStoreId()
    {
        return $this->_storeId;
    }

    public function isOptimizerActive($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED, $store);
    }

    /**
     * Return is active google optimizer for cms.
     * Return true if optimizer is enabled only for one store|website.
     *
     * @return boolean
     */
    public function isOptimizerActiveForCms()
    {
        if (!is_null($this->_activeForCmsFlag)) {
            return $this->_activeForCmsFlag;
        }
        $stores = array_merge(
            array(0),
            array_keys(Mage::getSingleton('Mage_Core_Model_System_Store')->getStoreCollection())
        );
        foreach ($stores as $store) {
            if ($this->isOptimizerActive($store)) {
                $this->_activeForCmsFlag = true;
                return $this->_activeForCmsFlag;
            }
        }
        $this->_activeForCmsFlag = false;
        return $this->_activeForCmsFlag;
    }

    /**
     * Prepare product attribute html output
     *
     * @param unknown_type $callObject
     * @param unknown_type $attributeHtml
     * @param unknown_type $params
     * @return unknown
     */
    public function productAttribute($callObject, $attributeHtml, $params)
    {
        $attributeName  = $params['attribute'];
        $product        = $params['product'];

        if (!$this->isOptimizerActive($product->getStoreId())
            || !$product->getGoogleOptimizerScripts()
            || !$product->getGoogleOptimizerScripts()->getControlScript()) {
            return $attributeHtml;
        }
        if (in_array($attributeName, $product->getGoogleOptimizerScripts()->getAttributes())) {
            $newAttributeName = 'product_'.$attributeName.'_'.$product->getId();
            if (strlen($newAttributeName) > self::MAX_ATTRIBUTE_LENGTH_LIMIT) {
                $newAttributeName = 'product_';
                $newAttributeName .= substr($attributeName, 0, (self::MAX_ATTRIBUTE_LENGTH_LIMIT - strlen('product__'.$product->getId())));
                $newAttributeName .= '_'.$product->getId();
            }
            $attributeHtml = '<script type="text/javascript">utmx_section("'.$newAttributeName.'")</script>' . $attributeHtml . '</noscript>';
        }
        return $attributeHtml;
    }

    /**
     * Prepare category attribute html output
     *
     * @param unknown_type $callObject
     * @param unknown_type $attributeHtml
     * @param unknown_type $params
     * @return unknown
     */
    public function categoryAttribute($callObject, $attributeHtml, $params)
    {
        $attributeName  = $params['attribute'];
        $category       = $params['category'];

        if (!$this->isOptimizerActive($category->getStoreId())
            || !$category->getGoogleOptimizerScripts()
            || !$category->getGoogleOptimizerScripts()->getControlScript()) {
            return $attributeHtml;
        }

        $newAttributeName = 'category_'.$attributeName.'_'.$category->getId();
        if (strlen($newAttributeName) > self::MAX_ATTRIBUTE_LENGTH_LIMIT) {
            $newAttributeName = 'category_';
            $newAttributeName .= substr($attributeName, 0, (self::MAX_ATTRIBUTE_LENGTH_LIMIT - strlen('category__' . $category->getId())));
            $newAttributeName .= '_' . $category->getId();
        }

        $attributeHtml = '<script>utmx_section("' . $newAttributeName . '")</script>' . $attributeHtml . '</noscript>';
        return $attributeHtml;
    }

    /**
     * Return conversion pages from source model
     *
     * @return Varien_Object
     */
    public function getConversionPagesUrl()
    {
        /**
         * Example:
         *
         * array(
         *  'checkout_cart' => 'http://base.url/...'
         * )
         */
        $urls = array();
        $choices = Mage::getModel('Mage_GoogleOptimizer_Model_Adminhtml_System_Config_Source_Googleoptimizer_Conversionpages')
            ->toOptionArray();
        $url = Mage::getModel('Mage_Core_Model_Url');
        $session = Mage::getSingleton('Mage_Core_Model_Session')->setSkipSessionIdFlag(true);
        $store = Mage::app()->getStore($this->getStoreId());
        foreach ($choices as $choice) {
            $route = '';
            switch ($choice['value']) {
                case 'checkout_cart':
                    $route = 'checkout/cart';
                    break;
                case 'checkout_onepage':
                    $route = 'checkout/onepage';
                    break;
                case 'checkout_multishipping':
                    $route = 'checkout/multishipping';
                    break;
                case 'checkout_onepage_success':
                    $route = 'checkout/onepage/success/';
                    break;
                case 'checkout_multishipping_success':
                    $route = 'checkout/multishipping/success/';
                    break;
                case 'customer_account_create':
                    $route = 'customer/account/create/';
                    break;
            }
            if ($route) {
                $_query = array();
                $_path = Mage_Core_Model_Url::XML_PATH_UNSECURE_URL;
                if (Mage::getConfig()->shouldUrlBeSecure('/' . $route)) {
                    $_path = Mage_Core_Model_Url::XML_PATH_SECURE_URL;
                }
                $storeBaseUrl = $store->getConfig($_path);
                $websiteBaseUrl = $store->getWebsite()->getConfig($_path);
                $defaultBaseUrl = Mage::app()->getStore(0)->getConfig($_path);
                if ($storeBaseUrl == $websiteBaseUrl && !Mage::app()->isSingleStoreMode()) {
                    $_query = array('__store' => $store->getCode());
                }
                $urls[$choice['value']] = $url->setStore($this->getStoreId())->getUrl($route,
                    array('_secure' => true, '_query' => $_query)
                );
            }
        }
        $session->setSkipSessionIdFlag(false);
        return new Varien_Object($urls);
    }

    /**
     * Create array of attributes for variation
     * allowed by googleoptimizer config and user defined attributes
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getProductAttributes(Varien_Object $product)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $allowedAttributes = array_keys(Mage::getConfig()->getNode(self::XML_PATH_ALLOWED_ATTRIBUTES)->asArray());
        $productAttributes = $product->getAttributes();
        $optimizerAttributes = array();
        foreach ($productAttributes as $_attributeCode => $_attribute) {
            if ($_attribute->getIsUserDefined() && $_attribute->getIsVisibleOnFront()) {
                $optimizerAttributes[] = array(
                    'label' => $_attribute->getFrontendLabel(),
                    'value' => $_attributeCode
                );
            } elseif (in_array($_attributeCode, $allowedAttributes)) {
                $optimizerAttributes[] = array(
                    'label' => $_attribute->getFrontendLabel(),
                    'value' => $_attributeCode
                );
            }
        }
        return $optimizerAttributes;
    }
}
