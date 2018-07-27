<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget;

/**
 * Backend form widget
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Form extends \Magento\Backend\Block\Widget
{
    /**
     * Form Object
     *
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setDestElementId('edit_form');
        $this->setShowGlobalIcon(false);
    }

    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changing layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        \Magento\Framework\Data\Form::setElementRenderer(
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Renderer\Element',
                $this->getNameInLayout() . '_element'
            )
        );
        \Magento\Framework\Data\Form::setFieldsetRenderer(
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Renderer\Fieldset',
                $this->getNameInLayout() . '_fieldset'
            )
        );
        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout() . '_fieldset_element'
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Get form object
     *
     * @return \Magento\Framework\Data\Form
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        if (is_object($this->getForm())) {
            return $this->getForm()->getHtml();
        }
        return '';
    }

    /**
     * Set form object
     *
     * @param \Magento\Framework\Data\Form $form
     * @return $this
     */
    public function setForm(\Magento\Framework\Data\Form $form)
    {
        $this->_form = $form;
        $this->_form->setParent($this);
        $this->_form->setBaseUrl($this->_urlBuilder->getBaseUrl());

        $customAttributes = $this->getData('custom_attributes');
        if (is_array($customAttributes)) {
            foreach ($customAttributes as $key => $value) {
                $this->_form->addCustomAttribute($key, $value);
            }
        }
        return $this;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        return $this;
    }

    /**
     * This method is called before rendering HTML
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_prepareForm();
        $this->_initFormValues();
        return parent::_beforeToHtml();
    }

    /**
     * Initialize form fields values
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        return $this;
    }

    /**
     * Set Fieldset to Form
     *
     * @param array $attributes attributes that are to be added
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param array $exclude attributes that should be skipped
     * @return void
     */
    protected function _setFieldset($attributes, $fieldset, $exclude = [])
    {
        $this->_addElementTypes($fieldset);
        foreach ($attributes as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            if (!$this->_isAttributeVisible($attribute)) {
                continue;
            }
            if (($inputType = $attribute->getFrontend()->getInputType()) && !in_array(
                $attribute->getAttributeCode(),
                $exclude
            ) && ('media_image' != $inputType || $attribute->getAttributeCode() == 'image')
            ) {
                $fieldType = $inputType;
                $rendererClass = $attribute->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType = $inputType . '_' . $attribute->getAttributeCode();
                    $fieldset->addType($fieldType, $rendererClass);
                }

                $element = $fieldset->addField(
                    $attribute->getAttributeCode(),
                    $fieldType,
                    [
                        'name' => $attribute->getAttributeCode(),
                        'label' => $attribute->getFrontend()->getLocalizedLabel(),
                        'class' => $attribute->getFrontend()->getClass(),
                        'required' => $attribute->getIsRequired(),
                        'note' => $attribute->getNote()
                    ]
                )->setEntityAttribute(
                    $attribute
                );

                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));

                $this->_applyTypeSpecificConfig($inputType, $element, $attribute);
            }
        }
    }

    /**
     * Check whether attribute is visible
     *
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return bool
     */
    protected function _isAttributeVisible(\Magento\Eav\Model\Entity\Attribute $attribute)
    {
        return !(!$attribute || $attribute->hasIsVisible() && !$attribute->getIsVisible());
    }

    /**
     * Apply configuration specific for different element type
     *
     * @param string $inputType
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return void
     */
    protected function _applyTypeSpecificConfig($inputType, $element, \Magento\Eav\Model\Entity\Attribute $attribute)
    {
        switch ($inputType) {
            case 'select':
                $element->setValues($attribute->getSource()->getAllOptions(true, true));
                break;
            case 'multiselect':
                $element->setValues($attribute->getSource()->getAllOptions(false, true));
                $element->setCanBeEmpty(true);
                break;
            case 'date':
                $element->setDateFormat($this->_localeDate->getDateFormatWithLongYear());
                break;
            case 'multiline':
                $element->setLineCount($attribute->getMultilineCount());
                break;
            default:
                break;
        }
    }

    /**
     * Add new element type
     *
     * @param \Magento\Framework\Data\Form\AbstractForm $baseElement
     * @return void
     */
    protected function _addElementTypes(\Magento\Framework\Data\Form\AbstractForm $baseElement)
    {
        $types = $this->_getAdditionalElementTypes();
        foreach ($types as $code => $className) {
            $baseElement->addType($code, $className);
        }
    }

    /**
     * Retrieve predefined additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return [];
    }

    /**
     * Render additional element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getAdditionalElementHtml($element)
    {
        return '';
    }
}
