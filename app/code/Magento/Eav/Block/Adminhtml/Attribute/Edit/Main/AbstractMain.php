<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Product attribute add/edit form main tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Block\Adminhtml\Attribute\Edit\Main;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Class \Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain
 *
 */
abstract class AbstractMain extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Attribute instance
     *
     * @var Attribute
     */
    protected $_attribute = null;

    /**
     * Eav data
     *
     * @var \Magento\Eav\Helper\Data
     */
    protected $_eavData = null;

    /**
     * @var \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker
     */
    protected $propertyLocker;

    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    protected $_yesnoFactory;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory
     */
    protected $_inputTypeFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Eav\Helper\Data $eavData
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Helper\Data $eavData,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        array $data = []
    ) {
        $this->_eavData = $eavData;
        $this->_yesnoFactory = $yesnoFactory;
        $this->_inputTypeFactory = $inputTypeFactory;
        $this->propertyLocker = $propertyLocker;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Set attribute object
     *
     * @param Attribute $attribute
     * @return $this
     * @codeCoverageIgnore
     */
    public function setAttributeObject($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Return attribute object
     *
     * @return Attribute
     */
    public function getAttributeObject()
    {
        if (null === $this->_attribute) {
            return $this->_coreRegistry->registry('entity_attribute');
        }
        return $this->_attribute;
    }

    /**
     * Preparing default form elements for editing attribute
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Attribute Properties')]);

        if ($attributeObject->getAttributeId()) {
            $fieldset->addField('attribute_id', 'hidden', ['name' => 'attribute_id']);
        }

        $this->_addElementTypes($fieldset);

        $yesno = $this->_yesnoFactory->create()->toOptionArray();

        $labels = $attributeObject->getFrontendLabel();
        $fieldset->addField(
            'attribute_label',
            'text',
            [
                'name' => 'frontend_label[0]',
                'label' => __('Default Label'),
                'title' => __('Default label'),
                'required' => true,
                'value' => is_array($labels) ? $labels[0] : $labels
            ]
        );

        $validateClass = sprintf(
            'validate-code validate-length maximum-length-%d',
            \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );
        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name' => 'attribute_code',
                'label' => __('Attribute Code'),
                'title' => __('Attribute Code'),
                'note' => __(
                    'This is used internally. Make sure you don\'t use spaces or more than %1 symbols.',
                    \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
                ),
                'class' => $validateClass,
                'required' => true
            ]
        );

        $fieldset->addField(
            'frontend_input',
            'select',
            [
                'name' => 'frontend_input',
                'label' => __('Catalog Input Type for Store Owner'),
                'title' => __('Catalog Input Type for Store Owner'),
                'value' => 'text',
                'values' => $this->_inputTypeFactory->create()->toOptionArray()
            ]
        );

        $fieldset->addField(
            'is_required',
            'select',
            [
                'name' => 'is_required',
                'label' => __('Values Required'),
                'title' => __('Values Required'),
                'values' => $yesno
            ]
        );

        $fieldset->addField(
            'default_value_text',
            'text',
            [
                'name' => 'default_value_text',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $attributeObject->getDefaultValue()
            ]
        );

        $fieldset->addField(
            'default_value_yesno',
            'select',
            [
                'name' => 'default_value_yesno',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'values' => $yesno,
                'value' => $attributeObject->getDefaultValue()
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'default_value_date',
            'date',
            [
                'name' => 'default_value_date',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $attributeObject->getDefaultValue(),
                'date_format' => $dateFormat
            ]
        );

        $fieldset->addField(
            'default_value_textarea',
            'textarea',
            [
                'name' => 'default_value_textarea',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $attributeObject->getDefaultValue()
            ]
        );

        $fieldset->addField(
            'is_unique',
            'select',
            [
                'name' => 'is_unique',
                'label' => __('Unique Value'),
                'title' => __('Unique Value (not shared with other products)'),
                'note' => __('Not shared with other products.'),
                'values' => $yesno
            ]
        );

        $fieldset->addField(
            'frontend_class',
            'select',
            [
                'name' => 'frontend_class',
                'label' => __('Input Validation for Store Owner'),
                'title' => __('Input Validation for Store Owner'),
                'values' => $this->_eavData->getFrontendClasses($attributeObject->getEntityType()->getEntityTypeCode())
            ]
        );

        if ($attributeObject->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
            $form->getElement('frontend_input')->setDisabled(1);
            if (!$attributeObject->getIsUserDefined()) {
                $form->getElement('is_unique')->setDisabled(1);
            }
        }

        $this->propertyLocker->lock($form);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->_eventManager->dispatch(
            'adminhtml_block_eav_attribute_edit_form_init',
            ['form' => $this->getForm()]
        );
        $this->getForm()->addValues($this->getAttributeObject()->getData());
        return parent::_initFormValues();
    }

    /**
     * Processing block html after rendering
     * Adding js block to the end of this block
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        $jsScripts = $this->getLayout()->createBlock(\Magento\Eav\Block\Adminhtml\Attribute\Edit\Js::class)
            ->toHtml();
        return $html . $jsScripts;
    }
}
