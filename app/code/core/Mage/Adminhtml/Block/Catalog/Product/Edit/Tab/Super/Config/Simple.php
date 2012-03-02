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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Quiq simple product creation
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Simple
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes
{
    /**
     * Link to currently editing product
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->setFieldNameSuffix('simple_product');
        $form->setDataObject($this->_getProduct());

        $fieldset = $form->addFieldset('simple_product', array(
            'legend' => Mage::helper('Mage_Catalog_Helper_Data')->__('Quick simple product creation')
        ));
        $this->_addElementTypes($fieldset);
        $attributesConfig = array(
            'autogenerate' => array('name', 'sku'),
            'additional'   => array('name', 'sku', 'visibility', 'status')
        );

        $availableTypes = array('text', 'select', 'multiselect', 'textarea', 'price', 'weight');

        $attributes = Mage::getModel('Mage_Catalog_Model_Product')
            ->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->setAttributeSetId($this->_getProduct()->getAttributeSetId())
            ->getAttributes();

        /* Standart attributes */
        foreach ($attributes as $attribute) {
            if (($attribute->getIsRequired()
                && $attribute->getApplyTo()
                // If not applied to configurable
                && !in_array(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, $attribute->getApplyTo())
                // If not used in configurable
                && !in_array($attribute->getId(),
                    $this->_getProduct()->getTypeInstance()->getUsedProductAttributeIds($this->_getProduct()))
                )
                // Or in additional
                || in_array($attribute->getAttributeCode(), $attributesConfig['additional'])
            ) {
                $inputType = $attribute->getFrontend()->getInputType();
                if (!in_array($inputType, $availableTypes)) {
                    continue;
                }
                $attributeCode = $attribute->getAttributeCode();
                $attribute->setAttributeCode('simple_product_' . $attributeCode);
                $element = $fieldset->addField(
                    'simple_product_' . $attributeCode,
                     $inputType,
                     array(
                        'label'    => $attribute->getFrontend()->getLabel(),
                        'name'     => $attributeCode,
                        'required' => $attribute->getIsRequired(),
                     )
                )->setEntityAttribute($attribute);

                if (in_array($attributeCode, $attributesConfig['autogenerate'])) {
                    $element->setDisabled('true');
                    $element->setValue($this->_getProduct()->getData($attributeCode));
                    $element->setAfterElementHtml(
                         '<input type="checkbox" id="simple_product_' . $attributeCode . '_autogenerate" '
                         . 'name="simple_product[' . $attributeCode . '_autogenerate]" value="1" '
                         . 'onclick="toggleValueElements(this, this.parentNode)" checked="checked" /> '
                         . '<label for="simple_product_' . $attributeCode . '_autogenerate" >'
                         . Mage::helper('Mage_Catalog_Helper_Data')->__('Autogenerate')
                         . '</label>'
                    );
                }


                if ($inputType == 'select' || $inputType == 'multiselect') {
                    $element->setValues($attribute->getFrontend()->getSelectOptions());
                }
            }

        }

        /* Configurable attributes */
        $usedAttributes = $this->_getProduct()->getTypeInstance()->getUsedProductAttributes($this->_getProduct());
        foreach ($usedAttributes as $attribute) {
            $attributeCode =  $attribute->getAttributeCode();
            $fieldset->addField( 'simple_product_' . $attributeCode, 'select',  array(
                'label' => $attribute->getFrontend()->getLabel(),
                'name'  => $attributeCode,
                'values' => $attribute->getSource()->getAllOptions(true, true),
                'required' => true,
                'class'    => 'validate-configurable',
                'onchange' => 'superProduct.showPricing(this, \'' . $attributeCode . '\')'
            ));

            $fieldset->addField('simple_product_' . $attributeCode . '_pricing_value', 'hidden', array(
                'name' => 'pricing[' . $attributeCode . '][value]'
            ));

            $fieldset->addField('simple_product_' . $attributeCode . '_pricing_type', 'hidden', array(
                'name' => 'pricing[' . $attributeCode . '][is_percent]'
            ));
        }

        /* Inventory Data */
        $fieldset->addField('simple_product_inventory_qty', 'text', array(
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Qty'),
            'name'  => 'stock_data[qty]',
            'class' => 'validate-number',
            'required' => true,
            'value'  => 0
        ));

        $fieldset->addField('simple_product_inventory_is_in_stock', 'select', array(
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Stock Availability'),
            'name'  => 'stock_data[is_in_stock]',
            'values' => array(
                array('value'=>1, 'label'=> Mage::helper('Mage_Catalog_Helper_Data')->__('In Stock')),
                array('value'=>0, 'label'=> Mage::helper('Mage_Catalog_Helper_Data')->__('Out of Stock'))
            ),
            'value' => 1
        ));

        $stockHiddenFields = array(
            'use_config_min_qty'            => 1,
            'use_config_min_sale_qty'       => 1,
            'use_config_max_sale_qty'       => 1,
            'use_config_backorders'         => 1,
            'use_config_notify_stock_qty'   => 1,
            'is_qty_decimal'                => 0
        );

        foreach ($stockHiddenFields as $fieldName=>$fieldValue) {
            $fieldset->addField('simple_product_inventory_' . $fieldName, 'hidden', array(
                'name'  => 'stock_data[' . $fieldName .']',
                'value' => $fieldValue
            ));
        }


        $fieldset->addField('create_button', 'note', array(
            'text' => $this->getButtonHtml(
                Mage::helper('Mage_Catalog_Helper_Data')->__('Quick Create'),
                'superProduct.quickCreateNewProduct()',
                'save'
            )
        ));

        $this->setForm($form);
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        if (!$this->_product) {
            $this->_product = Mage::registry('current_product');
        }
        return $this->_product;
    }
} // Class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Simple End
