<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Sales Order Create Form Abstract Block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
abstract class AbstractForm extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Form factory
     *
     * @var \Magento\Framework\Data\FormFactory
     * @since 2.0.0
     */
    protected $_formFactory;

    /**
     * Data Form object
     *
     * @var \Magento\Framework\Data\Form
     * @since 2.0.0
     */
    protected $_form;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     * @since 2.0.0
     */
    protected $dataObjectProcessor;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        array $data = []
    ) {
        $this->_formFactory = $formFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Prepare global layout
     * Add renderers to \Magento\Framework\Data\Form
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        \Magento\Framework\Data\Form::setElementRenderer(
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Renderer\Element::class,
                $this->getNameInLayout() . '_element'
            )
        );
        \Magento\Framework\Data\Form::setFieldsetRenderer(
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Renderer\Fieldset::class,
                $this->getNameInLayout() . '_fieldset'
            )
        );
        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element::class,
                $this->getNameInLayout() . '_fieldset_element'
            )
        );

        return $this;
    }

    /**
     * Return Form object
     *
     * @return \Magento\Framework\Data\Form
     * @since 2.0.0
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
     * @since 2.0.0
     */
    abstract protected function _prepareForm();

    /**
     * Return array of additional form element types by type
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getAdditionalFormElementTypes()
    {
        return [
            'file' => \Magento\Customer\Block\Adminhtml\Form\Element\File::class,
            'image' => \Magento\Customer\Block\Adminhtml\Form\Element\Image::class,
            'boolean' => \Magento\Customer\Block\Adminhtml\Form\Element\Boolean::class
        ];
    }

    /**
     * Return array of additional form element renderers by element id
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getAdditionalFormElementRenderers()
    {
        return [
            'region' => $this->getLayout()->createBlock(
                \Magento\Customer\Block\Adminhtml\Edit\Renderer\Region::class
            )
        ];
    }

    /**
     * Add additional data to form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    protected function _addAdditionalFormElementData(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this;
    }

    /**
     * Add rendering EAV attributes to Form element
     *
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface[] $attributes
     * @param \Magento\Framework\Data\Form\AbstractForm $form
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function _addAttributesToForm($attributes, \Magento\Framework\Data\Form\AbstractForm $form)
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
                        'class' => $attribute->getFrontendClass(),
                        'required' => $attribute->isRequired()
                    ]
                );
                if ($inputType == 'multiline') {
                    $element->setLineCount($attribute->getMultilineCount());
                }
                $element->setEntityAttribute($attribute);
                $this->_addAdditionalFormElementData($element);

                if (!empty($renderers[$attribute->getAttributeCode()])) {
                    $element->setRenderer($renderers[$attribute->getAttributeCode()]);
                }

                if ($inputType == 'select' || $inputType == 'multiselect') {
                    $options = [];
                    foreach ($attribute->getOptions() as $optionData) {
                        $data = $this->dataObjectProcessor->buildOutputDataArray(
                            $optionData,
                            \Magento\Customer\Api\Data\OptionInterface::class
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
                } elseif ($inputType == 'date') {
                    $format = $this->_localeDate->getDateFormat(
                        \IntlDateFormatter::SHORT
                    );
                    $element->setDateFormat($format);
                }
            }
        }

        return $this;
    }

    /**
     * Return Form Elements values
     *
     * @return array
     * @since 2.0.0
     */
    public function getFormValues()
    {
        return [];
    }
}
