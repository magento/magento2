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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product Options xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Catalog_Product_Options extends Mage_XmlConnect_Block_Catalog
{
    const OPTION_TYPE_SELECT    = 'select';
    const OPTION_TYPE_CHECKBOX  = 'checkbox';
    const OPTION_TYPE_TEXT      = 'text';

    /**
     * Store supported product options xml renderers based on product types
     *
     * @var array
     */
    protected $_renderers = array();

    /**
     * Add new product options renderer
     *
     * @param string $type
     * @param string $renderer
     * @return Mage_XmlConnect_Block_Product_Options
     */
    public function addRenderer($type, $renderer)
    {
        if (!isset($this->_renderers[$type])) {
            $this->_renderers[$type] = $renderer;
        }
        return $this;
    }

    /**
     * Create produc custom options Mage_XmlConnect_Model_Simplexml_Element object
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function getProductCustomOptionsXmlObject(Mage_Catalog_Model_Product $product)
    {
        $xmlModel = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', array('data' => '<product></product>'));
        $optionsNode = $xmlModel->addChild('options');

        if (!$product->getId()) {
            return $xmlModel;
        }
        $xmlModel->addAttribute('id', $product->getId());
        if (!$product->isSaleable() || !sizeof($product->getOptions())) {
            return $xmlModel;
        }

        foreach ($product->getOptions() as $option) {
            $optionNode = $optionsNode->addChild('option');
            $type = $this->_getOptionTypeForXmlByRealType($option->getType());
            $code = 'options[' . $option->getId() . ']';
            if ($type == self::OPTION_TYPE_CHECKBOX) {
                $code .= '[]';
            }
            $optionNode->addAttribute('code', $code);
            $optionNode->addAttribute('type', $type);
            $optionNode->addAttribute('label', $xmlModel->escapeXml($option->getTitle()));
            if ($option->getIsRequire()) {
                $optionNode->addAttribute('is_required', 1);
            }

            /**
             * Process option price
             */
            $price = $option->getPrice();
            if ($price) {
                $optionNode->addAttribute('price', Mage::helper('Mage_XmlConnect_Helper_Data')->formatPriceForXml($price));
                $formattedPrice = Mage::app()->getStore($product->getStoreId())->formatPrice($price, false);
                $optionNode->addAttribute('formated_price', $formattedPrice);
            }
            if ($type == self::OPTION_TYPE_CHECKBOX || $type == self::OPTION_TYPE_SELECT) {
                foreach ($option->getValues() as $value) {
                    $valueNode = $optionNode->addChild('value');
                    $valueNode->addAttribute('code', $value->getId());
                    $valueNode->addAttribute('label', $xmlModel->escapeXml($value->getTitle()));

                    if ($value->getPrice() != 0) {
                        $price = Mage::helper('Mage_XmlConnect_Helper_Data')->formatPriceForXml($value->getPrice());
                        $valueNode->addAttribute('price', $price);
                        $formattedPrice = $this->_formatPriceString($price, $product);
                        $valueNode->addAttribute('formated_price', $formattedPrice);
                    }
                }
            }
        }
        return $xmlModel;
    }

    /**
     * Format price with currency code and taxes
     *
     * @param string|int|float $price
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function _formatPriceString($price, $product)
    {
        $priceTax       = Mage::helper('Mage_Tax_Helper_Data')->getPrice($product, $price);
        $priceIncTax    = Mage::helper('Mage_Tax_Helper_Data')->getPrice($product, $price, true);

        if (Mage::helper('Mage_Tax_Helper_Data')->displayBothPrices() && $priceTax != $priceIncTax) {
            $formatted = Mage::helper('Mage_Core_Helper_Data')->currency($priceTax, true, false) . ' (+'
                . Mage::helper('Mage_Core_Helper_Data')->currency($priceIncTax, true, false) . ' '
                . Mage::helper('Mage_Tax_Helper_Data')->__('Incl. Tax') . ')';
        } else {
            $formatted = $this->helper('Mage_Core_Helper_Data')->currency($priceTax, true, false);
        }

        return $formatted;
    }

    /**
     * Retrieve option type name by specified option real type name
     *
     * @param string $realType
     * @return string
     */
    protected function _getOptionTypeForXmlByRealType($realType)
    {
        switch ($realType) {
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN:
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
                $type = self::OPTION_TYPE_SELECT;
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE:
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                $type = self::OPTION_TYPE_CHECKBOX;
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD:
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_AREA:
            default:
                $type = self::OPTION_TYPE_TEXT;
                break;
        }
        return $type;
    }

    /**
     * Create product custom options Mage_XmlConnect_Model_Simplexml_Element object
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_XmlConnect_Model_Simplexml_Element | false
     */
    public function getProductOptionsXmlObject(Mage_Catalog_Model_Product $product)
    {
        if ($product->getId()) {
            $type = $product->getTypeId();
            if (isset($this->_renderers[$type])) {
                $renderer = $this->getLayout()->createBlock($this->_renderers[$type]);
                if ($renderer) {
                    return $renderer->getProductOptionsXml($product, true);
                }
            }
        }
        return false;
    }

    /**
     * Generate product options xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        $productId = $this->getRequest()->getParam('id', null);
        $product = Mage::getModel('Mage_Catalog_Model_Product')->setStoreId(Mage::app()->getStore()->getId());

        if ($productId) {
            $product->load($productId);
        }

        if ($product->getId()) {
            $type = $product->getTypeId();
            if (isset($this->_renderers[$type])) {
                $renderer = $this->getLayout()->createBlock($this->_renderers[$type]);
                if ($renderer) {
                    return $renderer->getProductOptionsXml($product);
                }
            }
        }
        return '<?xml version="1.0" encoding="UTF-8"?><options/>';
    }
}
