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
 * Product attribute add/edit form main tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Main_Abstract
{
    /**
     * Adding product form elements for editing attribute
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $attributeObject = $this->getAttributeObject();
        /* @var $form Varien_Data_Form */
        $form = $this->getForm();
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset = $form->getElement('base_fieldset');

        $frontendInputElm = $form->getElement('frontend_input');
        $additionalTypes = array(
            array(
                'value' => 'price',
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Price')
            ),
            array(
                'value' => 'media_image',
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Media Image')
            )
        );
        if ($attributeObject->getFrontendInput() == 'gallery') {
            $additionalTypes[] = array(
                'value' => 'gallery',
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Gallery')
            );
        }

        $response = new Varien_Object();
        $response->setTypes(array());
        Mage::dispatchEvent('adminhtml_product_attribute_types', array('response'=>$response));
        $_disabledTypes = array();
        $_hiddenFields = array();
        foreach ($response->getTypes() as $type) {
            $additionalTypes[] = $type;
            if (isset($type['hide_fields'])) {
                $_hiddenFields[$type['value']] = $type['hide_fields'];
            }
            if (isset($type['disabled_types'])) {
                $_disabledTypes[$type['value']] = $type['disabled_types'];
            }
        }
        Mage::register('attribute_type_hidden_fields', $_hiddenFields);
        Mage::register('attribute_type_disabled_types', $_disabledTypes);

        $frontendInputValues = array_merge($frontendInputElm->getValues(), $additionalTypes);
        $frontendInputElm->setValues($frontendInputValues);

        $yesnoSource = Mage::getModel('Mage_Adminhtml_Model_System_Config_Source_Yesno')->toOptionArray();

        $scopes = array(
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE =>Mage::helper('Mage_Catalog_Helper_Data')->__('Store View'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE =>Mage::helper('Mage_Catalog_Helper_Data')->__('Website'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL =>Mage::helper('Mage_Catalog_Helper_Data')->__('Global'),
        );

        if ($attributeObject->getAttributeCode() == 'status' || $attributeObject->getAttributeCode() == 'tax_class_id') {
            unset($scopes[Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE]);
        }

        $fieldset->addField('is_global', 'select', array(
            'name'  => 'is_global',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Scope'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Scope'),
            'note'  => Mage::helper('Mage_Catalog_Helper_Data')->__('Declare attribute value saving scope'),
            'values'=> $scopes
        ), 'attribute_code');

        $fieldset->addField('apply_to', 'apply', array(
            'name'        => 'apply_to[]',
            'label'       => Mage::helper('Mage_Catalog_Helper_Data')->__('Apply To'),
            'values'      => Mage_Catalog_Model_Product_Type::getOptions(),
            'mode_labels' => array(
                'all'     => Mage::helper('Mage_Catalog_Helper_Data')->__('All Product Types'),
                'custom'  => Mage::helper('Mage_Catalog_Helper_Data')->__('Selected Product Types')
            ),
            'required'    => true
        ), 'frontend_class');

        $fieldset->addField('is_configurable', 'select', array(
            'name' => 'is_configurable',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Use To Create Configurable Product'),
            'values' => $yesnoSource,
        ), 'apply_to');

        // frontend properties fieldset
        $fieldset = $form->addFieldset('front_fieldset', array('legend'=>Mage::helper('Mage_Catalog_Helper_Data')->__('Frontend Properties')));

        $fieldset->addField('is_searchable', 'select', array(
            'name'     => 'is_searchable',
            'label'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Use in Quick Search'),
            'title'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Use in Quick Search'),
            'values'   => $yesnoSource,
        ));

        $fieldset->addField('is_visible_in_advanced_search', 'select', array(
            'name' => 'is_visible_in_advanced_search',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Use in Advanced Search'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Use in Advanced Search'),
            'values' => $yesnoSource,
        ));

        $fieldset->addField('is_comparable', 'select', array(
            'name' => 'is_comparable',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Comparable on Front-end'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Comparable on Front-end'),
            'values' => $yesnoSource,
        ));

        $fieldset->addField('is_filterable', 'select', array(
            'name' => 'is_filterable',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__("Use In Layered Navigation"),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Can be used only with catalog input type Dropdown, Multiple Select and Price'),
            'note' => Mage::helper('Mage_Catalog_Helper_Data')->__('Can be used only with catalog input type Dropdown, Multiple Select and Price'),
            'values' => array(
                array('value' => '0', 'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('No')),
                array('value' => '1', 'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Filterable (with results)')),
                array('value' => '2', 'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Filterable (no results)')),
            ),
        ));

        $fieldset->addField('is_filterable_in_search', 'select', array(
            'name' => 'is_filterable_in_search',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__("Use In Search Results Layered Navigation"),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Can be used only with catalog input type Dropdown, Multiple Select and Price'),
            'note' => Mage::helper('Mage_Catalog_Helper_Data')->__('Can be used only with catalog input type Dropdown, Multiple Select and Price'),
            'values' => $yesnoSource,
        ));

        $fieldset->addField('is_used_for_promo_rules', 'select', array(
            'name' => 'is_used_for_promo_rules',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Use for Promo Rule Conditions'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Use for Promo Rule Conditions'),
            'values' => $yesnoSource,
        ));

        $fieldset->addField('position', 'text', array(
            'name' => 'position',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Position'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Position in Layered Navigation'),
            'note' => Mage::helper('Mage_Catalog_Helper_Data')->__('Position of attribute in layered navigation block'),
            'class' => 'validate-digits',
        ));

        $fieldset->addField('is_wysiwyg_enabled', 'select', array(
            'name' => 'is_wysiwyg_enabled',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Enable WYSIWYG'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Enable WYSIWYG'),
            'values' => $yesnoSource,
        ));

        $htmlAllowed = $fieldset->addField('is_html_allowed_on_front', 'select', array(
            'name' => 'is_html_allowed_on_front',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Allow HTML Tags on Frontend'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Allow HTML Tags on Frontend'),
            'values' => $yesnoSource,
        ));
        if (!$attributeObject->getId() || $attributeObject->getIsWysiwygEnabled()) {
            $attributeObject->setIsHtmlAllowedOnFront(1);
        }

        $fieldset->addField('is_visible_on_front', 'select', array(
            'name'      => 'is_visible_on_front',
            'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Visible on Product View Page on Front-end'),
            'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Visible on Product View Page on Front-end'),
            'values'    => $yesnoSource,
        ));

        $fieldset->addField('used_in_product_listing', 'select', array(
            'name'      => 'used_in_product_listing',
            'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Used in Product Listing'),
            'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Used in Product Listing'),
            'note'      => Mage::helper('Mage_Catalog_Helper_Data')->__('Depends on design theme'),
            'values'    => $yesnoSource,
        ));
        $fieldset->addField('used_for_sort_by', 'select', array(
            'name'      => 'used_for_sort_by',
            'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Used for Sorting in Product Listing'),
            'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Used for Sorting in Product Listing'),
            'note'      => Mage::helper('Mage_Catalog_Helper_Data')->__('Depends on design theme'),
            'values'    => $yesnoSource,
        ));

        $form->getElement('apply_to')->setSize(5);

        if ($applyTo = $attributeObject->getApplyTo()) {
            $applyTo = is_array($applyTo) ? $applyTo : explode(',', $applyTo);
            $form->getElement('apply_to')->setValue($applyTo);
        } else {
            $form->getElement('apply_to')->addClass('no-display ignore-validate');
        }

        // define field dependencies
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Form_Element_Dependence')
                ->addFieldMap("is_wysiwyg_enabled", 'wysiwyg_enabled')
                ->addFieldMap("is_html_allowed_on_front", 'html_allowed_on_front')
                ->addFieldMap("frontend_input", 'frontend_input_type')
                ->addFieldDependence('wysiwyg_enabled', 'frontend_input_type', 'textarea')
                ->addFieldDependence('html_allowed_on_front', 'wysiwyg_enabled', '0')
        );

        Mage::dispatchEvent('adminhtml_catalog_product_attribute_edit_prepare_form', array(
            'form'      => $form,
            'attribute' => $attributeObject
        ));

        return $this;
    }

    /**
     * Retrieve additional element types for product attributes
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'apply' => Mage::getConfig()->getBlockClassName('Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Apply'),
        );
    }
}
