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
 * Adminhtml Tax Rule Edit Form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Tax_Rule_Edit_Form extends Mage_Backend_Block_Widget_Form
{
    /**
     * Init class
     *
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('taxRuleForm');
        $this->setTitle(Mage::helper('Mage_Tax_Helper_Data')->__('Tax Rule Information'));
        $this->setUseContainer(true);
    }

    /**
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model  = Mage::registry('tax_rule');
        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('Mage_Tax_Helper_Data')->__('Tax Rule Information')
        ));

        $rates = Mage::getModel('Mage_Tax_Model_Calculation_Rate')
            ->getCollection()
            ->toOptionArray();

         $fieldset->addField('code', 'text',
            array(
                'name'      => 'code',
                'label'     => Mage::helper('Mage_Tax_Helper_Data')->__('Name'),
                'class'     => 'required-entry',
                'required'  => true,
            )
        );

        // Editable multiselect for customer tax class
        $selectConfig = $this->getTaxClassSelectConfig(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER);
        $selectedCustomerTax = $model->getId()
            ? $model->getCustomerTaxClasses()
            : $model->getCustomerTaxClassWithDefault();
        $fieldset->addField($this->getTaxClassSelectHtmlId(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER),
            'editablemultiselect',
            array(
                'name' => $this->getTaxClassSelectHtmlId(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER),
                'label' => Mage::helper('Mage_Tax_Helper_Data')->__('Customer Tax Class'),
                'class' => 'required-entry',
                'values' => $model->getAllOptionsForClass(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER),
                'value' => $selectedCustomerTax,
                'required' => true,
                'select_config' => $selectConfig,
            ),
            false,
            true
        );

        // Editable multiselect for product tax class
        $selectConfig = $this->getTaxClassSelectConfig(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT);
        $selectedProductTax = $model->getId()
            ? $model->getProductTaxClasses()
            : $model->getProductTaxClassWithDefault();
        $fieldset->addField($this->getTaxClassSelectHtmlId(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT),
            'editablemultiselect',
            array(
                'name' => $this->getTaxClassSelectHtmlId(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT),
                'label' => Mage::helper('Mage_Tax_Helper_Data')->__('Product Tax Class'),
                'class' => 'required-entry',
                'values' => $model->getAllOptionsForClass(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT),
                'value' => $selectedProductTax,
                'required' => true,
                'select_config' => $selectConfig
            ),
            false,
            true
        );

        $fieldset->addField('tax_rate',
            'editablemultiselect',
            array(
                'name' => 'tax_rate',
                'label' => Mage::helper('Mage_Tax_Helper_Data')->__('Tax Rate'),
                'class' => 'required-entry',
                'values' => $rates,
                'value' => $model->getRates(),
                'required' => true,
                'element_js_class' => 'TaxRateEditableMultiselect',
                'select_config' => array('is_entity_editable' => true),
            )
        );

        $fieldset->addField('priority', 'text',
            array(
                'name'      => 'priority',
                'label'     => Mage::helper('Mage_Tax_Helper_Data')->__('Priority'),
                'class'     => 'validate-not-negative-number',
                'value'     => (int) $model->getPriority(),
                'required'  => true,
                'note'      => Mage::helper('Mage_Tax_Helper_Data')->__('Tax rates at the same priority are added, others are compounded.'),
            ),
            false,
            true
        );
        $fieldset->addField('position', 'text',
            array(
                'name'      => 'position',
                'label'     => Mage::helper('Mage_Tax_Helper_Data')->__('Sort Order'),
                'class'     => 'validate-not-negative-number',
                'value'     => (int) $model->getPosition(),
                'required'  => true,
            ),
            false,
            true
        );

        if ($model->getId() > 0 ) {
            $fieldset->addField('tax_calculation_rule_id', 'hidden',
                array(
                    'name'      => 'tax_calculation_rule_id',
                    'value'     => $model->getId(),
                    'no_span'   => true
                )
            );
        }

        $form->addValues($model->getData());
        $form->setAction($this->getUrl('*/tax_rule/save'));
        $form->setUseContainer($this->getUseContainer());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve HTML element ID for corresponding tax class selector
     *
     * @param string $classType
     * @return string
     */
    public function getTaxClassSelectHtmlId($classType)
    {
        return 'tax_' . strtolower($classType) . '_class';
    }


    /**
     * Retrieve configuration options for tax class editable multiselect
     *
     * @param string $classType
     * @return array
     */
    public function getTaxClassSelectConfig($classType)
    {
        $config = array(
            'new_url' => $this->getUrl('adminhtml/tax_class/ajaxSave/'),
            'save_url' => $this->getUrl('adminhtml/tax_class/ajaxSave/'),
            'delete_url' => $this->getUrl('adminhtml/tax_class/ajaxDelete/'),
            'delete_confirm_message' => Mage::helper('Mage_Tax_Helper_Data')->__('Do you really want to delete this tax class?'),
            'target_select_id' => $this->getTaxClassSelectHtmlId($classType),
            'add_button_caption' => Mage::helper('Mage_Tax_Helper_Data')->__('Add New Tax Class'),
            'submit_data' => array(
                'class_type' => $classType,
                'form_key' => Mage::getSingleton('Mage_Core_Model_Session')->getFormKey(),
            ),
            'entity_id_name' => 'class_id',
            'entity_value_name' => 'class_name',
            'is_entity_editable' => true
        );
        return $config;
    }

    /**
     * Retrieve Tax Rate delete URL
     *
     * @return string
     */
    public function getTaxRateDeleteUrl()
    {
        return $this->getUrl('adminhtml/tax_rate/ajaxDelete/');
    }

    /**
     * Retrieve Tax Rate save URL
     *
     * @return string
     */
    public function getTaxRateSaveUrl()
    {
        return $this->getUrl('adminhtml/tax_rate/ajaxSave/');
    }
}
