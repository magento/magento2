<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget;

use Magento\Backend\Block\Widget\Form\Element\ElementCreator;
use Magento\Framework\App\ObjectManager;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form as DataForm;
use Magento\Backend\Block\Widget\Form\Renderer\Element;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element as FieldsetElement;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\AbstractForm;

/**
 * Backend form widget
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
class Form extends \Magento\Backend\Block\Widget
{
    /**
     * Form Object
     *
     * @var DataForm
     */
    protected $_form;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form.phtml';

    /**
     * @var ElementCreator
     * /
    private $creator;

    /**
     * Constructs form
     *
     * @param Context $context
     * @param array $data
     * @param ElementCreator|null $creator
     */
    public function __construct(
        Context $context,
        array $data = [],
        ElementCreator $creator = null
    ) {
        parent::__construct($context, $data);
        $this->creator = $creator ?: ObjectManager::getInstance()->get(ElementCreator::class);
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
        DataForm::setElementRenderer(
            $this->getLayout()->createBlock(
                Element::class,
                $this->getNameInLayout() . '_element'
            )
        );
        DataForm::setFieldsetRenderer(
            $this->getLayout()->createBlock(
                Fieldset::class,
                $this->getNameInLayout() . '_fieldset'
            )
        );
        DataForm::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                FieldsetElement::class,
                $this->getNameInLayout() . '_fieldset_element'
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Get form object
     *
     * @return DataForm
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
     * @param DataForm $form
     * @return $this
     */
    public function setForm(DataForm $form)
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
     *
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
            /* @var $attribute Attribute */
            if (!$this->_isAttributeVisible($attribute)) {
                continue;
            }
            if (($inputType = $attribute->getFrontend()->getInputType())
                && !in_array($attribute->getAttributeCode(), $exclude)
                && ('media_image' !== $inputType || $attribute->getAttributeCode() == 'image')
            ) {
                $element = $this->creator->create($fieldset, $attribute);
                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));

                $this->_applyTypeSpecificConfig($inputType, $element, $attribute);
            }
        }
    }

    /**
     * Check whether attribute is visible
     *
     * @param Attribute $attribute
     * @return bool
     */
    protected function _isAttributeVisible(Attribute $attribute)
    {
        return !(!$attribute || $attribute->hasIsVisible() && !$attribute->getIsVisible());
    }

    /**
     * Apply configuration specific for different element type
     *
     * @param string $inputType
     * @param AbstractElement $element
     * @param Attribute $attribute
     * @return void
     */
    protected function _applyTypeSpecificConfig($inputType, $element, Attribute $attribute)
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
     * @param AbstractForm $baseElement
     * @return void
     */
    protected function _addElementTypes(AbstractForm $baseElement)
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
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getAdditionalElementHtml($element)
    {
        return '';
    }
}
