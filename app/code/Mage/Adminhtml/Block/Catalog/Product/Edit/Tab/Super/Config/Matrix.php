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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product variations matrix block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Matrix
    extends Mage_Backend_Block_Template
{
    /** @var Mage_Core_Model_App */
    protected $_application;

    /** @var Mage_Core_Model_LocaleInterface */
    protected $_locale;

    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_Core_Model_App $application
     * @param Mage_Core_Model_LocaleInterface $locale
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Core_Model_App $application,
        Mage_Core_Model_LocaleInterface $locale,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_application = $application;
        $this->_locale = $locale;
    }

    /**
     * Retrieve price rendered according to current locale and currency settings
     *
     * @param int|float $price
     * @return string
     */
    public function renderPrice($price)
    {
        return $this->_locale->currency($this->_application->getBaseCurrencyCode())->toCurrency(sprintf('%f', $price));
    }

    /**
     * Get configurable product type
     *
     * @return Mage_Catalog_Model_Product_Type_Configurable
     */
    protected function _getProductType()
    {
        return Mage::getSingleton('Mage_Catalog_Model_Product_Type_Configurable');
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * Retrieve all possible attribute values combinations
     *
     * @return array
     */
    public function getVariations()
    {
        $variationalAttributes = array();
        $usedProductAttributes = $this->getAttributes();
        foreach ($usedProductAttributes as $attribute) {
            $options = array();
            foreach ($attribute['options'] as $valueInfo) {
                foreach ($attribute['values'] as $priceData) {
                    if ($priceData['value_index'] == $valueInfo['value']
                        && (!isset($priceData['include']) || $priceData['include'])
                    ) {
                        $valueInfo['price'] = $priceData;
                        $options[] = $valueInfo;
                    }
                }
            }
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $variationalAttributes[] = array(
                'id' => $attribute['attribute_id'],
                'values' => $options,
            );
        }

        $attributesCount = count($variationalAttributes);
        if ($attributesCount === 0) {
            return array();
        }

        $variations = array();
        $currentVariation = array_fill(0, $attributesCount, 0);
        $variationalAttributes = array_reverse($variationalAttributes);
        $lastAttribute = $attributesCount - 1;
        do {
            for ($attributeIndex = 0; $attributeIndex < $attributesCount - 1; ++$attributeIndex) {
                if ($currentVariation[$attributeIndex] >= count($variationalAttributes[$attributeIndex]['values'])) {
                    $currentVariation[$attributeIndex] = 0;
                    ++$currentVariation[$attributeIndex + 1];
                }
            }
            if ($currentVariation[$lastAttribute] >= count($variationalAttributes[$lastAttribute]['values'])) {
                break;
            }

            $filledVariation = array();
            for ($attributeIndex = $attributesCount; $attributeIndex--;) {
                $currentAttribute = $variationalAttributes[$attributeIndex];
                $filledVariation[$currentAttribute['id']] =
                    $currentAttribute['values'][$currentVariation[$attributeIndex]];
            }

            $variations[] = $filledVariation;
            $currentVariation[0]++;
        } while (1);
        return $variations;
    }

    /**
     * Get url for product edit
     *
     * @param $id
     *
     * @return string
     */
    public function getEditProductUrl($id)
    {
        return $this->getUrl('*/*/edit', array('id' => $id));
    }


    /**
     * Retrieve attributes data
     *
     * @return array
     */
    public function getAttributes()
    {
        if (!$this->hasData('attributes')) {
            $attributes = (array)$this->_getProductType()->getConfigurableAttributesAsArray($this->getProduct());
            $productData = (array)$this->getRequest()->getParam('product');
            if (isset($productData['configurable_attributes_data'])) {
                $configurableData = $productData['configurable_attributes_data'];
                foreach ($attributes as $key => &$attribute) {
                    if (isset($configurableData[$key])) {
                        $attribute['values'] = array_merge(
                            isset($attribute['values']) ? $attribute['values'] : array(),
                            isset($configurableData[$key]['values'])
                                ? array_filter($configurableData[$key]['values'])
                                : array()
                        );
                    }
                }
            }
            $this->setData('attributes', $attributes);
        }
        return $this->getData('attributes');
    }

    /**
     * Get used product attributes
     *
     * @return array
     */
    public function getUsedAttributes()
    {
        return $this->_getProductType()->getUsedProductAttributes($this->getProduct());
    }

    /**
     * Retrieve actual list of associated products, array key is obtained from varying attributes values
     *
     * @return array
     */
    public function getAssociatedProducts()
    {
        $productByUsedAttributes = array();
        foreach ($this->_getAssociatedProducts() as $product) {
            $keys = array();
            foreach ($this->getUsedAttributes() as $attribute) {
                /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                $keys[] = $product->getData($attribute->getAttributeCode());
            }
            $productByUsedAttributes[implode('-', $keys)] = $product;
        }
        return $productByUsedAttributes;
    }

    /**
     * Retrieve actual list of associated products (i.e. if product contains variations matrix form data
     * - previously saved in database relations are not considered)
     *
     * @return array
     */
    protected function _getAssociatedProducts()
    {
        $product = $this->getProduct();
        $ids = $this->getProduct()->getAssociatedProductIds();
        if ($ids === null) { // form data overrides any relations stored in database
            return $this->_getProductType()->getUsedProducts($product);
        }
        $products = array();
        foreach ($ids as $productId) {
            /** @var $product Mage_Catalog_Model_Product */
            $product = Mage::getModel('Mage_Catalog_Model_Product')->load($productId);
            if ($product->getId()) {
                $products[] = $product;
            }
        }
        return $products;
    }

    /**
     * Get html class for attribute
     *
     * @param string $code
     * @return string
     */
    public function getAttributeFrontendClass($code)
    {
        /** @var $config Mage_Catalog_Model_Config */
        $config = Mage::getSingleton('Mage_Catalog_Model_Config');
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $config->getAttribute(Mage_Catalog_Model_Product::ENTITY, $code);
        return $attribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract
            ? $attribute->getFrontend()->getClass()
            : '';
    }

    /**
     * Get url to upload files
     *
     * @return string
     */
    public function getImageUploadUrl()
    {
        return $this->getUrl('*/catalog_product_gallery/upload');
    }
}
