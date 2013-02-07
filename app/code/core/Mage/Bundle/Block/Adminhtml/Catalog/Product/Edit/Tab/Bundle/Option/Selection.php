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
 * @package     Mage_Bundle
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Bundle selection renderer
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Bundle_Option_Selection extends Mage_Adminhtml_Block_Widget
{
    protected $_template = 'product/edit/bundle/option/selection.phtml';

    /**
     * Initialize bundle option selection block
     */
    protected function _construct()
    {

        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }

    /**
     * Return field id
     *
     * @return string
     */
    public function getFieldId()
    {
        return 'bundle_selection';
    }

    /**
     * Return field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'bundle_selections';
    }

    /**
     * Prepare block layout
     *
     * @return Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Bundle_Option_Selection
     */
    protected function _prepareLayout()
    {
        $this->addChild('selection_delete_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Delete'),
            'class' => 'delete icon-btn',
            'on_click' => 'bSelection.remove(event)'
        ));
        return parent::_prepareLayout();
    }

    /**
     * Retrieve delete button html
     *
     * @return string
     */
    public function getSelectionDeleteButtonHtml()
    {
        return $this->getChildHtml('selection_delete_button');
    }

    /**
     * Retrieve price type select html
     *
     * @return string
     */
    public function getPriceTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Html_Select')
            ->setData(array(
                'id'    => $this->getFieldId() . '_{{index}}_price_type',
                'class' => 'select select-product-option-type required-option-select'
            ))
            ->setName($this->getFieldName() . '[{{parentIndex}}][{{index}}][selection_price_type]')
            ->setOptions(Mage::getSingleton('Mage_Bundle_Model_Source_Option_Selection_Price_Type')->toOptionArray());
        if ($this->getCanEditPrice() === false) {
            $select->setExtraParams('disabled="disabled"');
        }
        return $select->getHtml();
    }

    /**
     * Retrieve qty type select html
     *
     * @return string
     */
    public function getQtyTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Html_Select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{index}}_can_change_qty',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{parentIndex}}][{{index}}][selection_can_change_qty]')
            ->setOptions(Mage::getSingleton('Mage_Backend_Model_Config_Source_Yesno')->toOptionArray());

        return $select->getHtml();
    }

    /**
     * Return search url
     *
     * @return string
     */
    public function getSelectionSearchUrl()
    {
        return $this->getUrl('*/bundle_selection/grid');
    }

    /**
     * Check if used website scope price
     *
     * @return string
     */
    public function isUsedWebsitePrice()
    {
        return !Mage::helper('Mage_Catalog_Helper_Data')->isPriceGlobal() && Mage::registry('product')->getStoreId();
    }

    /**
     * Retrieve price scope checkbox html
     *
     * @return string
     */
    public function getCheckboxScopeHtml()
    {
        $checkboxHtml = '';
        if ($this->isUsedWebsitePrice()) {
            $id = $this->getFieldId() . '_{{index}}_price_scope';
            $name = $this->getFieldName() . '[{{parentIndex}}][{{index}}][default_price_scope]';
            $class = 'bundle-option-price-scope-checkbox';
            $label = Mage::helper('Mage_Bundle_Helper_Data')->__('Use Default Value');
            $disabled = ($this->getCanEditPrice() === false) ? ' disabled="disabled"' : '';
            $checkboxHtml = '<input type="checkbox" id="' . $id . '" class="' . $class . '" name="' . $name
                . '"' . $disabled . ' value="1" />';
            $checkboxHtml .= '<label class="normal" for="' . $id . '">' . $label . '</label>';
        }
        return $checkboxHtml;
    }
}
