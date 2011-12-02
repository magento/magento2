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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product attributes tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Attribute_New_Product_Attributes extends Mage_Adminhtml_Block_Catalog_Form
{
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        /**
         * Initialize product object as form property
         * for using it in elements generation
         */
        $form->setDataObject(Mage::registry('product'));

        $fieldset = $form->addFieldset('group_fields', array());

        $attributes = $this->getGroupAttributes();

        $this->_setFieldset($attributes, $fieldset, array('gallery'));

        $values = Mage::registry('product')->getData();
        /**
         * Set attribute default values for new product
         */
        if (!Mage::registry('product')->getId()) {
            foreach ($attributes as $attribute) {
                if (!isset($values[$attribute->getAttributeCode()])) {
                    $values[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                }
            }
        }

        Mage::dispatchEvent('adminhtml_catalog_product_edit_prepare_form', array('form'=>$form));
        $form->addValues($values);
        $form->setFieldNameSuffix('product');
        $this->setForm($form);
    }

    protected function _getAdditionalElementTypes()
    {
        $result = array(
            'price'   => Mage::getConfig()
                ->getBlockClassName('Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Price'),
            'image'   => Mage::getConfig()
                ->getBlockClassName('Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Image'),
            'boolean' => Mage::getConfig()
                ->getBlockClassName('Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Boolean')
        );

        $response = new Varien_Object();
        $response->setTypes(array());
        Mage::dispatchEvent('adminhtml_catalog_product_edit_element_types', array('response'=>$response));

        foreach ($response->getTypes() as $typeName=>$typeClass) {
            $result[$typeName] = $typeClass;
        }

        return $result;
    }

    protected function _toHtml()
    {
        parent::_toHtml();
        return $this->getForm()->getElement('group_fields')->getChildrenHtml();
    }
}
