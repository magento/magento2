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
 * Adminhtml customers wishlist grid item renderer for name/options cell
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_View_Grid_Renderer_Item
    extends Mage_Backend_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Returns helper for product type
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Helper_Product_Configuration_Interface
     */
    protected function _getProductHelper($product)
    {
        // Retrieve whole array of renderers
        $productHelpers = $this->getProductHelpers();
        if (!is_array($productHelpers)) {
            $column = $this->getColumn();
            if ($column) {
                $grid = $column->getGrid();
                if ($grid) {
                    $productHelpers = $grid->getProductConfigurationHelpers();
                    $this->setProductHelpers($productHelpers ? $productHelpers : array());
                }
            }
        }

        // Check whether we have helper for our product
        $productType = $product->getTypeId();
        if (isset($productHelpers[$productType])) {
            $helperName = $productHelpers[$productType];
        } else if (isset($productHelpers['default'])) {
            $helperName = $productHelpers['default'];
        } else {
            $helperName = 'Mage_Catalog_Helper_Product_Configuration';
        }

        $helper = Mage::helper($helperName);
        if (!($helper instanceof Mage_Catalog_Helper_Product_Configuration_Interface)) {
            Mage::throwException($this->__("Helper for options rendering doesn't implement required interface."));
        }

        return $helper;
    }

    /*
     * Returns product associated with this block
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getProduct()
    {
        return $this->getItem()->getProduct();
    }

    /**
     * Returns list of options and their values for product configuration
     *
     * @return array
     */
    protected function getOptionList()
    {
        $item = $this->getItem();
        $product = $item->getProduct();
        $helper = $this->_getProductHelper($product);
        return $helper->getOptions($item);
    }

    /**
     * Returns formatted option value for an item
     *
     * @param Mage_Wishlist_Item_Option
     * @return array
     */
    protected function getFormattedOptionValue($option)
    {
        $params = array(
            'max_length' => 55
        );
        return Mage::helper('Mage_Catalog_Helper_Product_Configuration')->getFormattedOptionValue($option, $params);
    }

    /*
     * Renders item product name and its configuration
     *
     * @param Mage_Catalog_Model_Product_Configuration_Item_Interface $item
     * @return string
     */
    public function render(Varien_Object $item)
    {
        $this->setItem($item);
        $product = $this->getProduct();
        $options = $this->getOptionList();
        return $options ? $this->_renderItemOptions($product, $options) : $this->escapeHtml($product->getName());
    }

    /**
     * Render product item with options
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $options
     * @return string
     */
    protected function _renderItemOptions(Mage_Catalog_Model_Product $product, array $options)
    {
        $html = '<div class="bundle-product-options">'
            . '<strong>' . $this->escapeHtml($product->getName()) . '</strong>'
            . '<dl>';
        foreach ($options as $option) {
            $formattedOption = $this->getFormattedOptionValue($option);
            $html .= '<dt>' . $this->escapeHtml($option['label']) . '</dt>';
            $html .= '<dd>' . $this->escapeHtml($formattedOption['value']) . '</dd>';
        }
        $html .= '</dl></div>';

        return $html;
    }
}
