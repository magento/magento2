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
 * Product attributes tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes extends Mage_Adminhtml_Block_Catalog_Form
{
    /**
     * Load Wysiwyg on demand and prepare layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::helper('Mage_Catalog_Helper_Data')->isModuleEnabled('Mage_Cms')
            && Mage::getSingleton('Mage_Cms_Model_Wysiwyg_Config')->isEnabled()
        ) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    /**
     * Prepare attributes form
     *
     * @return null
     */
    protected function _prepareForm()
    {
        $group = $this->getGroup();
        if ($group) {
            $form = new Varien_Data_Form();

            // Initialize product object as form property to use it during elements generation
            $form->setDataObject(Mage::registry('product'));

            $fieldset = $form->addFieldset('group_fields' . $group->getId(), array(
                'legend' => Mage::helper('Mage_Catalog_Helper_Data')->__($group->getAttributeGroupName()),
                'class' => 'fieldset-wide'
            ));

            $attributes = $this->getGroupAttributes();

            $this->_setFieldset($attributes, $fieldset, array('gallery'));

            $urlKey = $form->getElement('url_key');
            if ($urlKey) {
                $urlKey->setRenderer(
                    $this->getLayout()->createBlock('Mage_Adminhtml_Block_Catalog_Form_Renderer_Attribute_Urlkey')
                );
            }

            $tierPrice = $form->getElement('tier_price');
            if ($tierPrice) {
                $tierPrice->setRenderer(
                    $this->getLayout()->createBlock('Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Tier')
                );
            }

            $groupPrice = $form->getElement('group_price');
            if ($groupPrice) {
                $groupPrice->setRenderer(
                    $this->getLayout()->createBlock('Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Group')
                );
            }

            $recurringProfile = $form->getElement('recurring_profile');
            if ($recurringProfile) {
                $recurringProfile->setRenderer(
                    $this->getLayout()->createBlock('Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Recurring')
                );
            }

            // Add new attribute button if it is not an image tab
            if (!$form->getElement('media_gallery')
                && Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('catalog/attributes/attributes')
            ) {
                $headerBar = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes_Create');

                $headerBar->getConfig()
                    ->setTabId('group_' . $group->getId())
                    ->setGroupId($group->getId())
                    ->setStoreId($form->getDataObject()->getStoreId())
                    ->setAttributeSetId($form->getDataObject()->getAttributeSetId())
                    ->setTypeId($form->getDataObject()->getTypeId())
                    ->setProductId($form->getDataObject()->getId());

                $fieldset->setHeaderBar($headerBar->toHtml());
            }

            if ($form->getElement('meta_description')) {
                $form->getElement('meta_description')->setOnkeyup('checkMaxLength(this, 255);');
            }

            $values = Mage::registry('product')->getData();

            // Set default attribute values for new product
            if (!Mage::registry('product')->getId()) {
                foreach ($attributes as $attribute) {
                    if (!isset($values[$attribute->getAttributeCode()])) {
                        $values[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                    }
                }
            }

            if (Mage::registry('product')->hasLockedAttributes()) {
                foreach (Mage::registry('product')->getLockedAttributes() as $attribute) {
                    $element = $form->getElement($attribute);
                    if ($element) {
                        $element->setReadonly(true, true);
                    }
                }
            }
            $form->addValues($values);
            $form->setFieldNameSuffix('product');

            Mage::dispatchEvent('adminhtml_catalog_product_edit_prepare_form', array('form' => $form));

            $this->setForm($form);
        }
    }

    /**
     * Retrieve additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $result = array(
            'price'    => Mage::getConfig()->getBlockClassName('Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Price'),
            'weight'   => Mage::getConfig()->getBlockClassName('Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Weight'),
            'gallery'  => Mage::getConfig()->getBlockClassName('Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery'),
            'image'    => Mage::getConfig()->getBlockClassName('Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Image'),
            'boolean'  => Mage::getConfig()->getBlockClassName('Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Boolean'),
            'textarea' => Mage::getConfig()->getBlockClassName('Mage_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg')
        );

        $response = new Varien_Object();
        $response->setTypes(array());
        Mage::dispatchEvent('adminhtml_catalog_product_edit_element_types', array('response' => $response));

        foreach ($response->getTypes() as $typeName => $typeClass) {
            $result[$typeName] = $typeClass;
        }

        return $result;
    }
}
