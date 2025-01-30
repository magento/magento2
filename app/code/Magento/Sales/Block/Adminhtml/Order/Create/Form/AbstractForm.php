<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

use IntlDateFormatter;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Element;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Block\Adminhtml\Edit\Renderer\Region;
use Magento\Customer\Block\Adminhtml\Form\Element\Boolean;
use Magento\Customer\Block\Adminhtml\Form\Element\File;
use Magento\Customer\Block\Adminhtml\Form\Element\Image;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate;
use Magento\Sales\Model\AdminOrder\Create;

/**
 * Sales Order Create Form Abstract Block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractForm extends AbstractCreate
{
    /**
     * @var FormFactory
     */
    protected $_formFactory;

    /**
     * Data Form object
     *
     * @var Form
     */
    protected $_form;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param FormFactory $formFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param array $data
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        FormFactory $formFactory,
        DataObjectProcessor $dataObjectProcessor,
        array $data = []
    ) {
        $this->_formFactory = $formFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Prepare global layout. Add renderers to \Magento\Framework\Data\Form
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        Form::setElementRenderer(
            $this->getLayout()->createBlock(
                Element::class,
                $this->getNameInLayout() . '_element'
            )
        );
        Form::setFieldsetRenderer(
            $this->getLayout()->createBlock(
                Fieldset::class,
                $this->getNameInLayout() . '_fieldset'
            )
        );
        Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                Fieldset\Element::class,
                $this->getNameInLayout() . '_fieldset_element'
            )
        );

        return $this;
    }

    /**
     * Return Form object
     *
     * @return Form
     * @throws LocalizedException
     */
    public function getForm()
    {
        if ($this->_form === null) {
            $this->_form = $this->_formFactory->create();
            $this->_prepareForm();
        }
        return $this->_form;
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    abstract protected function _prepareForm();

    /**
     * Return array of additional form element types by type
     *
     * @return array
     */
    protected function _getAdditionalFormElementTypes()
    {
        return [
            'file' => File::class,
            'image' => Image::class,
            'boolean' => Boolean::class
        ];
    }

    /**
     * Return array of additional form element renderers by element id
     *
     * @return array
     * @throws LocalizedException
     */
    protected function _getAdditionalFormElementRenderers()
    {
        return [
            'region' => $this->getLayout()->createBlock(
                Region::class
            )
        ];
    }

    /**
     * Add additional data to form element
     *
     * @param AbstractElement $element
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _addAdditionalFormElementData(AbstractElement $element)
    {
        return $this;
    }

    /**
     * Add rendering EAV attributes to Form element
     *
     * @param AttributeMetadataInterface[] $attributes
     * @param Form\AbstractForm $form
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws LocalizedException
     */
    protected function _addAttributesToForm($attributes, Form\AbstractForm $form)
    {
        // add additional form types
        $types = $this->_getAdditionalFormElementTypes();
        foreach ($types as $type => $className) {
            $form->addType($type, $className);
        }
        $renderers = $this->_getAdditionalFormElementRenderers();

        foreach ($attributes as $attribute) {
            $inputType = $attribute->getFrontendInput();

            if ($inputType) {
                $element = $form->addField(
                    $attribute->getAttributeCode(),
                    $inputType,
                    [
                        'name' => $attribute->getAttributeCode(),
                        'label' => __($attribute->getStoreLabel()),
                        'class' => $this->getValidationClasses($attribute),
                        'required' => $attribute->isRequired(),
                        'sort_order' => $attribute->getSortOrder()
                    ]
                );
                switch ($inputType) {
                    case 'multiline':
                        $element->setLineCount($attribute->getMultilineCount());
                        break;
                    case 'select':
                    case 'multiselect':
                        $this->addSelectOptions($attribute, $element);
                        break;
                    case 'date':
                        $format = $this->_localeDate->getDateFormat(
                            IntlDateFormatter::SHORT
                        );
                        $element->setDateFormat($format);
                        break;
                }
                $element->setEntityAttribute($attribute);
                $this->_addAdditionalFormElementData($element);

                if (!empty($renderers[$attribute->getAttributeCode()])) {
                    $element->setRenderer($renderers[$attribute->getAttributeCode()]);
                }
            }
        }

        return $this;
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        return [];
    }

    /**
     * Retrieve frontend classes according validation rules
     *
     * @param AttributeMetadataInterface $attribute
     *
     * @return string
     */
    private function getValidationClasses(AttributeMetadataInterface $attribute) : string
    {
        $out = [];
        $out[] = $attribute->getFrontendClass();

        $textClasses = $this->getTextLengthValidateClasses($attribute);
        if (!empty($textClasses)) {
            $out = array_merge($out, $textClasses);
        }

        return implode(' ', array_unique(array_filter($out)));
    }

    /**
     * Retrieve validation classes by min_text_length and max_text_length rules
     *
     * @param AttributeMetadataInterface $attribute
     *
     * @return array
     */
    private function getTextLengthValidateClasses(AttributeMetadataInterface $attribute) : array
    {
        $classes = [];

        $validateRules = $attribute->getValidationRules();
        if (!empty($validateRules)) {
            foreach ($validateRules as $rule) {
                switch ($rule->getName()) {
                    case 'min_text_length':
                        $classes[] = 'minimum-length-' . $rule->getValue();
                        break;

                    case 'max_text_length':
                        $classes[] = 'maximum-length-' . $rule->getValue();
                        break;
                }
            }

            if (!empty($classes)) {
                $classes[] = 'validate-length';
            }
        }

        return $classes;
    }

    /**
     * Add select options for SELECT and MULTISELECT attribute
     *
     * @param AttributeMetadataInterface $attribute
     * @param AbstractElement $element
     * @return void
     */
    private function addSelectOptions(AttributeMetadataInterface $attribute, AbstractElement $element): void
    {
        $options = [];
        foreach ($attribute->getOptions() as $optionData) {
            $data = $this->dataObjectProcessor->buildOutputDataArray(
                $optionData,
                OptionInterface::class
            );
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    unset($data[$key]);
                    $data['value'] = $value;
                }
            }
            $options[] = $data;
        }
        $element->setValues($options);
    }
}
