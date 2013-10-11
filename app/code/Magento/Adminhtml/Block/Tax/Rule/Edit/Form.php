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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml Tax Rule Edit Form
 */
namespace Magento\Adminhtml\Block\Tax\Rule\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Tax\Model\Calculation\RateFactory
     */
    protected $_rateFactory;

    /**
     * @param \Magento\Tax\Model\Calculation\RateFactory $rateFactory
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Data\Form\Factory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Model\Calculation\RateFactory $rateFactory,
        \Magento\Core\Model\Registry $registry,
        \Magento\Data\Form\Factory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_rateFactory = $rateFactory;
        parent::__construct($registry, $formFactory, $coreData, $context, $data);
    }

    /**
     * Init class
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('taxRuleForm');
        $this->setTitle(__('Tax Rule Information'));
        $this->setUseContainer(true);
    }

    /**
     * return \Magento\Adminhtml\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $model  = $this->_coreRegistry->registry('tax_rule');
        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create(array(
            'attributes' => array(
                'id'        => 'edit_form',
                'action'    => $this->getData('action'),
                'method'    => 'post',
            ))
        );

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend' => __('Tax Rule Information')
        ));

        $rates = $this->_rateFactory->create()
            ->getCollection()
            ->toOptionArray();

         $fieldset->addField('code', 'text',
            array(
                'name'      => 'code',
                'label'     => __('Name'),
                'class'     => 'required-entry',
                'required'  => true,
            )
        );

        // Editable multiselect for customer tax class
        $selectConfig = $this->getTaxClassSelectConfig(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER);
        $selectedCustomerTax = $model->getId()
            ? $model->getCustomerTaxClasses()
            : $model->getCustomerTaxClassWithDefault();
        $fieldset->addField($this->getTaxClassSelectHtmlId(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER),
            'editablemultiselect',
            array(
                'name' => $this->getTaxClassSelectHtmlId(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER),
                'label' => __('Customer Tax Class'),
                'class' => 'required-entry',
                'values' => $model->getAllOptionsForClass(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER),
                'value' => $selectedCustomerTax,
                'required' => true,
                'select_config' => $selectConfig,
            ),
            false,
            true
        );

        // Editable multiselect for product tax class
        $selectConfig = $this->getTaxClassSelectConfig(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT);
        $selectedProductTax = $model->getId()
            ? $model->getProductTaxClasses()
            : $model->getProductTaxClassWithDefault();
        $fieldset->addField($this->getTaxClassSelectHtmlId(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT),
            'editablemultiselect',
            array(
                'name' => $this->getTaxClassSelectHtmlId(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT),
                'label' => __('Product Tax Class'),
                'class' => 'required-entry',
                'values' => $model->getAllOptionsForClass(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT),
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
                'label' => __('Tax Rate'),
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
                'label'     => __('Priority'),
                'class'     => 'validate-not-negative-number',
                'value'     => (int) $model->getPriority(),
                'required'  => true,
                'note'      => __('Tax rates at the same priority are added, others are compounded.'),
            ),
            false,
            true
        );
        $fieldset->addField('position', 'text',
            array(
                'name'      => 'position',
                'label'     => __('Sort Order'),
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
            'new_url' => $this->getUrl('adminhtml/tax_tax/ajaxSave/'),
            'save_url' => $this->getUrl('adminhtml/tax_tax/ajaxSave/'),
            'delete_url' => $this->getUrl('adminhtml/tax_tax/ajaxDelete/'),
            'delete_confirm_message' => __('Do you really want to delete this tax class?'),
            'target_select_id' => $this->getTaxClassSelectHtmlId($classType),
            'add_button_caption' => __('Add New Tax Class'),
            'submit_data' => array(
                'class_type' => $classType,
                'form_key' => $this->_session->getFormKey(),
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
