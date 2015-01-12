<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product attribute add/edit form main tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Model\Config\Source\Yesno;
use Magento\Eav\Helper\Data;

class Advanced extends Generic
{
    /**
     * Eav data
     *
     * @var Data
     */
    protected $_eavData = null;

    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Yesno $yesNo
     * @param Data $eavData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        Yesno $yesNo,
        Data $eavData,
        array $data = []
    ) {
        $this->_yesNo = $yesNo;
        $this->_eavData = $eavData;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
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
                    'For internal use. Must be unique with no spaces. Maximum length of attribute code must be less than %1 symbols',
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

        $dateFormat = $this->_localeDate->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT);
        $fieldset->addField(
            'default_value_date',
            'date',
            [
                'name' => 'default_value_date',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'image' => $this->getViewFileUrl('images/grid-cal.gif'),
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
                'note' => __('Not shared with other products'),
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
            if (!$attributeObject->getIsUserDefined()) {
                $form->getElement('is_unique')->setDisabled(1);
            }
        }

        $scopes = [
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE => __('Store View'),
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_WEBSITE => __('Website'),
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL => __('Global'),
        ];

        if ($attributeObject->getAttributeCode() == 'status' || $attributeObject->getAttributeCode() == 'tax_class_id'
        ) {
            unset($scopes[\Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE]);
        }

        $fieldset->addField(
            'is_global',
            'select',
            [
                'name' => 'is_global',
                'label' => __('Scope'),
                'title' => __('Scope'),
                'note' => __('Declare attribute value saving scope'),
                'values' => $scopes
            ],
            'attribute_code'
        );

        $this->_eventManager->dispatch('product_attribute_form_build', ['form' => $form]);
        $this->setForm($form);
        return $this;
    }

    /**
     * Initialize form fileds values
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
     * @return mixed
     */
    private function getAttributeObject()
    {
        return $this->_coreRegistry->registry('entity_attribute');
    }
}
