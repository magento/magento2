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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Create Configurable product Settings Tab Block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Settings extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare block children and data
     *
     */
    protected function _prepareLayout()
    {
        $onclick = "jQuery('#product-edit-form').attr('action', "
            . $this->helper('Mage_Core_Helper_Data')->jsonEncode($this->getContinueUrl())
            . ").addClass('ignore-validate').submit();";
        $this->addChild('continue_button', 'Mage_Backend_Block_Widget_Button', array(
            'label'   => Mage::helper('Mage_Catalog_Helper_Data')->__('Generate Variations'),
            'onclick' => $onclick,
            'class'   => 'save',
        ));
        parent::_prepareLayout();
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Settings
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('settings', array(
            'legend'=>Mage::helper('Mage_Catalog_Helper_Data')->__('Select Configurable Attributes')
        ));

        $product    = $this->_getProduct();
        $attributes = $product->getTypeInstance()
            ->getSetAttributes($product);

        $hasAttributes = false;
        $usedAttributes = $product->isConfigurable()
            ? $this->_getProduct()->getTypeInstance()->getUsedProductAttributeIds($this->_getProduct())
            : array();

        $configurableType = Mage::getSingleton('Mage_Catalog_Model_Product_Type_Configurable');

        foreach ($attributes as $attribute) {
            if ($configurableType->canUseAttribute($attribute, $product)) {
                $hasAttributes = true;
                $fieldset->addField('attribute_' . $attribute->getAttributeId(), 'checkbox', array(
                    'label' => $attribute->getFrontend()->getLabel(),
                    'title' => $attribute->getFrontend()->getLabel(),
                    'name'  => 'attributes[]',
                    'class' => 'configurable-attribute-checkbox',
                    'value' => $attribute->getAttributeId(),
                    'checked' => in_array($attribute->getAttributeId(), $usedAttributes)
                ));
            }
        }

        if ($hasAttributes) {
            $fieldset->addField('continue_button', 'note', array(
                'text' => $this->getChildHtml('continue_button'),
            ));
        } else {
            $fieldset->addField('note_text', 'note', array(
                'text' => $this->__('This attribute set does not have attributes which we can use for configurable product')
            ));
        }


        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve Continue URL
     *
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->getUrl($this->_getProduct()->getId() ? '*/*/edit' : '*/*/new', array(
            '_current' => true,
        ));
    }

    /**
     * Retrieve Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/', array('set' => null, 'type' => null));
    }
}
