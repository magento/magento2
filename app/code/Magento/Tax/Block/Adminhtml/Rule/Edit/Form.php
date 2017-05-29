<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml Tax Rule Edit Form
 */
namespace Magento\Tax\Block\Adminhtml\Rule\Edit;

use Magento\Tax\Api\TaxClassManagementInterface;

/**
 * @api
 */
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
     * @var \Magento\Tax\Api\TaxRuleRepositoryInterface
     */
    protected $ruleService;

    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Tax\Model\Rate\Source $rateSource
     * @param \Magento\Tax\Api\TaxRuleRepositoryInterface $ruleService
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassService
     * @param \Magento\Tax\Model\TaxClass\Source\Customer $customerTaxClassSource
     * @param \Magento\Tax\Model\TaxClass\Source\Product $productTaxClassSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Tax\Model\Rate\Source $rateSource,
        \Magento\Tax\Api\TaxRuleRepositoryInterface $ruleService,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassService,
        \Magento\Tax\Model\TaxClass\Source\Customer $customerTaxClassSource,
        \Magento\Tax\Model\TaxClass\Source\Product $productTaxClassSource,
        array $data = []
    ) {
        $this->rateSource = $rateSource;
        $this->formKey = $context->getFormKey();
        $this->ruleService = $ruleService;
        $this->taxClassService = $taxClassService;
        $this->customerTaxClassSource = $customerTaxClassSource;
        $this->productTaxClassSource = $productTaxClassSource;
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $taxRuleId = $this->_coreRegistry->registry('tax_rule_id');
        try {
            $taxRule = $this->ruleService->get($taxRuleId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            /** Tax rule not found */
        }
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $sessionFormValues = (array)$this->_coreRegistry->registry('tax_rule_form_data');
        $taxRuleData = isset($taxRule) ? $this->extractTaxRuleData($taxRule) : [];
        $formValues = array_merge($taxRuleData, $sessionFormValues);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Tax Rule Information')]);

        $fieldset->addField(
            'code',
            'text',
            [
                'name' => 'code',
                'value' => isset($formValues['code']) ? $formValues['code'] : '',
                'label' => __('Name'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        // Editable multiselect for customer tax class
        $selectConfig = $this->getTaxClassSelectConfig(TaxClassManagementInterface::TYPE_CUSTOMER);
        $options = $this->customerTaxClassSource->getAllOptions(false);
        if (!empty($options)) {
            $selected = $options[0];
        } else {
            $selected = null;
        }

        // Use the rule data or pick the first class in the list
        $selectedCustomerTax = isset($formValues['tax_customer_class'])
            ? $formValues['tax_customer_class']
            : $selected;
        $fieldset->addField(
            'tax_customer_class',
            'editablemultiselect',
            [
                'name' => 'tax_customer_class',
                'label' => __('Customer Tax Class'),
                'class' => 'required-entry',
                'values' => $options,
                'value' => $selectedCustomerTax,
                'required' => true,
                'select_config' => $selectConfig
            ],
            false,
            true
        );

        // Editable multiselect for product tax class
        $selectConfig = $this->getTaxClassSelectConfig(TaxClassManagementInterface::TYPE_PRODUCT);
        $options = $this->productTaxClassSource->getAllOptions(false);
        if (!empty($options)) {
            $selected = $options[0];
        } else {
            $selected = null;
        }

        // Use the rule data or pick the first class in the list
        $selectedProductTax = isset($formValues['tax_product_class'])
            ? $formValues['tax_product_class']
            : $selected;
        $fieldset->addField(
            'tax_product_class',
            'editablemultiselect',
            [
                'name' => 'tax_product_class',
                'label' => __('Product Tax Class'),
                'class' => 'required-entry',
                'values' => $options,
                'value' => $selectedProductTax,
                'required' => true,
                'select_config' => $selectConfig
            ],
            false,
            true
        );

        $fieldset->addField(
            'tax_rate',
            'editablemultiselect',
            [
                'name' => 'tax_rate',
                'label' => __('Tax Rate'),
                'class' => 'required-entry',
                'values' => $this->rateSource->toOptionArray(),
                'value' => isset($formValues['tax_rate']) ? $formValues['tax_rate'] : [],
                'required' => true,
                'element_js_class' => 'TaxRateEditableMultiselect',
                'select_config' => ['is_entity_editable' => true]
            ]
        );

        $fieldset->addField(
            'priority',
            'text',
            [
                'name' => 'priority',
                'label' => __('Priority'),
                'class' => 'validate-not-negative-number',
                'value' => isset($formValues['priority']) ? $formValues['priority'] : 0,
                'required' => true,
                'note' => __('Tax rates at the same priority are added, others are compounded.')
            ],
            false,
            true
        );

        $fieldset->addField(
            'calculate_subtotal',
            'checkbox',
            [
                'name'  => 'calculate_subtotal',
                'label' => __('Calculate Off Subtotal Only'),
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                'checked' => isset($formValues['calculate_subtotal']) ? $formValues['calculate_subtotal'] : 0
            ],
            false,
            true
        );

        $fieldset->addField(
            'position',
            'text',
            [
                'name' => 'position',
                'label' => __('Sort Order'),
                'class' => 'validate-not-negative-number',
                'value' => isset($formValues['position']) ? $formValues['position'] : 0,
                'required' => true
            ],
            false,
            true
        );

        if (isset($taxRule)) {
            $fieldset->addField(
                'tax_calculation_rule_id',
                'hidden',
                ['name' => 'tax_calculation_rule_id', 'value' => $taxRule->getId(), 'no_span' => true]
            );
        }

        $form->setAction($this->getUrl('tax/rule/save'));
        $form->setUseContainer($this->getUseContainer());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve configuration options for tax class editable multiselect
     *
     * @param string $classType
     * @return array
     */
    public function getTaxClassSelectConfig($classType)
    {
        $config = [
            'new_url' => $this->getUrl('tax/tax/ajaxSave/'),
            'save_url' => $this->getUrl('tax/tax/ajaxSave/'),
            'delete_url' => $this->getUrl('tax/tax/ajaxDelete/'),
            'delete_confirm_message' => __('Do you really want to delete this tax class?'),
            'target_select_id' => 'tax_' . strtolower($classType) . '_class',
            'add_button_caption' => __('Add New Tax Class'),
            'submit_data' => ['class_type' => $classType, 'form_key' => $this->formKey->getFormKey()],
            'entity_id_name' => 'class_id',
            'entity_value_name' => 'class_name',
            'is_entity_editable' => true,
        ];
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
     * Retrieve Tax Rate load URL
     *
     * @return string
     */
    public function getTaxRateLoadUrl()
    {
        return $this->getUrl('tax/rate/ajaxLoad/');
    }

    /**
     * Extract tax rule data in a format which is
     *
     * @param \Magento\Tax\Api\Data\TaxRuleInterface $taxRule
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
            'position' => $taxRule->getPosition(),
            'calculate_subtotal' => $taxRule->getCalculateSubtotal(),
        ];
        return $taxRuleData;
    }
}
