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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml Tax Rule Edit Form
 */
namespace Magento\Tax\Block\Adminhtml\Rule\Edit;

use Magento\Tax\Service\V1\TaxClassServiceInterface;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Tax\Model\Rate\Source
     */
    protected $rateSource;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Tax\Service\V1\TaxRuleServiceInterface
     */
    protected $ruleService;

    /**
     * @var \Magento\Tax\Service\V1\TaxClassServiceInterface
     */
    protected $taxClassService;

    /**
     * @var \Magento\Tax\Model\TaxClass\Source\Customer
     */
    protected $customerTaxClassSource;

    /**
     * @var \Magento\Tax\Model\TaxClass\Source\Product
     */
    protected $productTaxClassSource;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Tax\Model\Rate\Source $rateSource
     * @param \Magento\Tax\Service\V1\TaxRuleServiceInterface $ruleService
     * @param \Magento\Tax\Service\V1\TaxClassServiceInterface $taxClassService
     * @param \Magento\Tax\Model\TaxClass\Source\Customer $customerTaxClassSource
     * @param \Magento\Tax\Model\TaxClass\Source\Product $productTaxClassSource
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Tax\Model\Rate\Source $rateSource,
        \Magento\Tax\Service\V1\TaxRuleServiceInterface $ruleService,
        \Magento\Tax\Service\V1\TaxClassServiceInterface $taxClassService,
        \Magento\Tax\Model\TaxClass\Source\Customer $customerTaxClassSource,
        \Magento\Tax\Model\TaxClass\Source\Product $productTaxClassSource,
        \Magento\Tax\Helper\Data $taxHelper,
        array $data = array()
    ) {
        $this->rateSource = $rateSource;
        $this->formKey = $context->getFormKey();
        $this->ruleService = $ruleService;
        $this->taxClassService = $taxClassService;
        $this->customerTaxClassSource = $customerTaxClassSource;
        $this->productTaxClassSource = $productTaxClassSource;
        $this->taxHelper = $taxHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init class
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('taxRuleForm');
        $this->setTitle(__('Tax Rule Information'));
        $this->setUseContainer(true);
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $taxRuleId = $this->_coreRegistry->registry('tax_rule_id');
        try {
            $taxRule = $this->ruleService->getTaxRule($taxRuleId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            /** Tax rule not found */
        }
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            array('data' => array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'))
        );
        $sessionFormValues = (array)$this->_coreRegistry->registry('tax_rule_form_data');
        $taxRuleData = isset($taxRule) ? $this->extractTaxRuleData($taxRule) : [];
        $formValues = array_merge($taxRuleData, $sessionFormValues);

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Tax Rule Information')));

        $fieldset->addField(
            'code',
            'text',
            array(
                'name' => 'code',
                'value' => isset($formValues['code']) ? $formValues['code'] : '',
                'label' => __('Name'),
                'class' => 'required-entry',
                'required' => true
            )
        );

        // Editable multiselect for customer tax class
        $selectConfig = $this->getTaxClassSelectConfig(TaxClassServiceInterface::TYPE_CUSTOMER);
        $selectedCustomerTax = isset($formValues['tax_customer_class'])
            ? $formValues['tax_customer_class']
            : $this->getDefaultCustomerTaxClass();
        $fieldset->addField(
            'tax_customer_class',
            'editablemultiselect',
            array(
                'name' => 'tax_customer_class',
                'label' => __('Customer Tax Class'),
                'class' => 'required-entry',
                'values' => $this->customerTaxClassSource->getAllOptions(false),
                'value' => $selectedCustomerTax,
                'required' => true,
                'select_config' => $selectConfig
            ),
            false,
            true
        );

        // Editable multiselect for product tax class
        $selectConfig = $this->getTaxClassSelectConfig(TaxClassServiceInterface::TYPE_PRODUCT);
        $selectedProductTax = isset($formValues['tax_product_class'])
            ? $formValues['tax_product_class']
            : $this->getDefaultProductTaxClass();
        $fieldset->addField(
            'tax_product_class',
            'editablemultiselect',
            array(
                'name' => 'tax_product_class',
                'label' => __('Product Tax Class'),
                'class' => 'required-entry',
                'values' => $this->productTaxClassSource->getAllOptions(false),
                'value' => $selectedProductTax,
                'required' => true,
                'select_config' => $selectConfig
            ),
            false,
            true
        );

        $fieldset->addField(
            'tax_rate',
            'editablemultiselect',
            array(
                'name' => 'tax_rate',
                'label' => __('Tax Rate'),
                'class' => 'required-entry',
                'values' => $this->rateSource->toOptionArray(),
                'value' => isset($formValues['tax_rate']) ? $formValues['tax_rate'] : [],
                'required' => true,
                'element_js_class' => 'TaxRateEditableMultiselect',
                'select_config' => array('is_entity_editable' => true)
            )
        );

        $fieldset->addField(
            'priority',
            'text',
            array(
                'name' => 'priority',
                'label' => __('Priority'),
                'class' => 'validate-not-negative-number',
                'value' => isset($formValues['priority']) ? $formValues['priority'] : 0,
                'required' => true,
                'note' => __('Tax rates at the same priority are added, others are compounded.')
            ),
            false,
            true
        );

        $fieldset->addField(
            'calculate_subtotal',
            'checkbox',
            array(
                'name'  => 'calculate_subtotal',
                'label' => __('Calculate Off Subtotal Only'),
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                'checked' => isset($formValues['calculate_subtotal']) ? $formValues['calculate_subtotal'] : 0
            ),
            false,
            true
        );

        $fieldset->addField(
            'position',
            'text',
            array(
                'name' => 'position',
                'label' => __('Sort Order'),
                'class' => 'validate-not-negative-number',
                'value' => isset($formValues['position']) ? $formValues['position'] : 0,
                'required' => true
            ),
            false,
            true
        );

        if (isset($taxRule)) {
            $fieldset->addField(
                'tax_calculation_rule_id',
                'hidden',
                array('name' => 'tax_calculation_rule_id', 'value' => $taxRule->getId(), 'no_span' => true)
            );
        }

        $form->setAction($this->getUrl('tax/rule/save'));
        $form->setUseContainer($this->getUseContainer());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Identify default customer tax class ID.
     *
     * @return int|null
     */
    public function getDefaultCustomerTaxClass()
    {
        $configValue = $this->taxHelper->getDefaultCustomerTaxClass();
        if (!empty($configValue)) {
            return $configValue;
        }
        $taxClasses = $this->customerTaxClassSource->getAllOptions(false);
        if (!empty($taxClasses)) {
            $firstClass = array_shift($taxClasses);
            return isset($firstClass['value']) ? $firstClass['value'] : null;
        } else {
            return null;
        }
    }

    /**
     * Identify default product tax class ID.
     *
     * @return int|null
     */
    public function getDefaultProductTaxClass()
    {
        $configValue = $this->taxHelper->getDefaultProductTaxClass();
        if (!empty($configValue)) {
            return $configValue;
        }
        $taxClasses = $this->productTaxClassSource->getAllOptions(false);
        if (!empty($taxClasses)) {
            $firstClass = array_shift($taxClasses);
            return isset($firstClass['value']) ? $firstClass['value'] : null;
        } else {
            return null;
        }
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
            'new_url' => $this->getUrl('tax/tax/ajaxSave/'),
            'save_url' => $this->getUrl('tax/tax/ajaxSave/'),
            'delete_url' => $this->getUrl('tax/tax/ajaxDelete/'),
            'delete_confirm_message' => __('Do you really want to delete this tax class?'),
            'target_select_id' => 'tax_' . strtolower($classType) . '_class',
            'add_button_caption' => __('Add New Tax Class'),
            'submit_data' => array('class_type' => $classType, 'form_key' => $this->formKey->getFormKey()),
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
        return $this->getUrl('tax/rate/ajaxDelete/');
    }

    /**
     * Retrieve Tax Rate save URL
     *
     * @return string
     */
    public function getTaxRateSaveUrl()
    {
        return $this->getUrl('tax/rate/ajaxSave/');
    }

    /**
     * Extract tax rule data in a format which is
     *
     * @param \Magento\Tax\Service\V1\Data\TaxRule $taxRule
     * @return array
     */
    protected function extractTaxRuleData($taxRule)
    {
        $taxRuleData = [
            'code' => $taxRule->getCode(),
            'tax_customer_class' => $taxRule->getCustomerTaxClassIds(),
            'tax_product_class' => $taxRule->getProductTaxClassIds(),
            'tax_rate' => $taxRule->getTaxRateIds(),
            'priority' => $taxRule->getPriority(),
            'position' => $taxRule->getSortOrder(),
            'calculate_subtotal' => $taxRule->getCalculateSubtotal()
        ];
        return $taxRuleData;
    }
}
