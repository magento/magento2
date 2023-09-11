<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Catalog\Model\Attribute\Source\ApplyTo;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;
use Magento\Eav\Helper\Data;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;

/**
 * Product attribute add/edit advanced form tab
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Advanced extends Generic
{
    /**
     * Eav data
     *
     * @var Data
     */
    protected $_eavData;

    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @var array
     */
    protected $disableScopeChangeList;

    /**
     * @var PropertyLocker
     */
    private $propertyLocker;

    /**
     * @var ApplyTo
     */
    private $applyTo;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param Data $eavData
     * @param array $disableScopeChangeList
     * @param array $data
     * @param PropertyLocker|null $propertyLocker
     * @param ApplyTo|null $applyTo
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        Data $eavData,
        array $disableScopeChangeList = [],
        array $data = [],
        ?PropertyLocker $propertyLocker = null,
        ?ApplyTo $applyTo = null
    ) {
        $this->_yesNo = $yesNo;
        $this->_eavData = $eavData;
        $this->disableScopeChangeList = $disableScopeChangeList;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->propertyLocker = $propertyLocker ?? ObjectManager::getInstance()->get(PropertyLocker::class);
        $this->applyTo = $applyTo ?? ObjectManager::getInstance()->get(ApplyTo::class);
    }

    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD)
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset(
            'advanced_fieldset',
            ['legend' => __('Advanced Attribute Properties'), 'collapsable' => true]
        );

        $yesno = $this->_yesNo->toOptionArray();

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
                'class' => $validateClass
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
                'date_format' => $dateFormat,
            ]
        );

        $timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'default_value_datetime',
            'date',
            [
                'name' => 'default_value_datetime',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $this->getLocalizedDateDefaultValue(),
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
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

        $fieldset->addField(
            'is_used_in_grid',
            'select',
            [
                'name' => 'is_used_in_grid',
                'label' => __('Add to Column Options'),
                'title' => __('Add to Column Options'),
                'values' => $yesno,
                'value' => $attributeObject->getData('is_used_in_grid') ?: 1,
                'note' => __('Select "Yes" to add this attribute to the list of column options in the product grid.'),
            ]
        );

        $fieldset->addField(
            'is_visible_in_grid',
            'hidden',
            [
                'name' => 'is_visible_in_grid',
                'value' => $attributeObject->getData('is_visible_in_grid') ?: 1,
            ]
        );

        $fieldset->addField(
            'is_filterable_in_grid',
            'select',
            [
                'name' => 'is_filterable_in_grid',
                'label' => __('Use in Filter Options'),
                'title' => __('Use in Filter Options'),
                'values' => $yesno,
                'value' => $attributeObject->getData('is_filterable_in_grid') ?: 1,
                'note' => __('Select "Yes" to add this attribute to the list of filter options in the product grid.'),
            ]
        );

        $fieldset->addField(
            'apply_to',
            'multiselect',
            [
                'name' => 'apply_to',
                'label' => __('Apply To'),
                'title' => __('Apply To'),
                'values' => $this->applyTo->toOptionArray(),
                'value' => $attributeObject->getApplyTo()
            ]
        );

        if ($attributeObject->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
            if (!$attributeObject->getIsUserDefined()) {
                $form->getElement('is_unique')->setDisabled(1);
                $form->getElement('apply_to')->setDisabled(1);
            }
        }

        $scopes = [
            ScopedAttributeInterface::SCOPE_STORE => __('Store View'),
            ScopedAttributeInterface::SCOPE_WEBSITE => __('Website'),
            ScopedAttributeInterface::SCOPE_GLOBAL => __('Global'),
        ];

        if ($attributeObject->getAttributeCode() == 'status' || $attributeObject->getAttributeCode() == 'tax_class_id'
        ) {
            unset($scopes[ScopedAttributeInterface::SCOPE_STORE]);
        }

        $fieldset->addField(
            'is_global',
            'select',
            [
                'name' => 'is_global',
                'label' => __('Scope'),
                'title' => __('Scope'),
                'note' => __('Declare attribute value saving scope.'),
                'values' => $scopes
            ],
            'attribute_code'
        );

        $this->_eventManager->dispatch('product_attribute_form_build', ['form' => $form]);
        if (in_array($attributeObject->getAttributeCode(), $this->disableScopeChangeList)) {
            $form->getElement('is_global')->setDisabled(1);
        }
        $this->setForm($form);
        $this->propertyLocker->lock($form);
        return $this;
    }

    /**
     * Initialize form fields values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getAttributeObject()->getData());
        return parent::_initFormValues();
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return Attribute
     */
    private function getAttributeObject()
    {
        return $this->_coreRegistry->registry('entity_attribute');
    }

    /**
     * Get localized date default value
     *
     * @return string
     * @throws LocalizedException
     */
    private function getLocalizedDateDefaultValue(): string
    {
        $attributeObject = $this->getAttributeObject();
        if (empty($attributeObject->getDefaultValue()) || $attributeObject->getFrontendInput() !== 'datetime') {
            return (string)$attributeObject->getDefaultValue();
        }

        try {
            $localizedDate = $this->_localeDate->date($attributeObject->getDefaultValue(), null, false);
            $localizedDate->setTimezone(new \DateTimeZone($this->_localeDate->getConfigTimezone()));
            $localizedDate = $localizedDate->format(DateTime::DATETIME_PHP_FORMAT);
        } catch (\Exception $e) {
            throw new LocalizedException(__('The default date is invalid.'));
        }

        return $localizedDate;
    }
}
