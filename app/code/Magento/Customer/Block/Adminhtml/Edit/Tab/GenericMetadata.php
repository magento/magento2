<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Api\Data\AttributeMetadataInterface;

/**
 * Generic block that uses customer metatdata attributes.
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class GenericMetadata extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        array $data = []
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Set Fieldset to Form
     *
     * @param AttributeMetadataInterface[] $attributes attributes that are to be added
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param array $exclude attributes that should be skipped
     * @return void
     */
    protected function _setFieldset($attributes, $fieldset, $exclude = [])
    {
        $this->_addElementTypes($fieldset);

        foreach ($attributes as $attribute) {
            // Note, ignoring whether its visible or not,
            if (($inputType = $attribute->getFrontendInput()) && !in_array(
                $attribute->getAttributeCode(),
                $exclude
            ) && ('media_image' != $inputType || $attribute->getAttributeCode() == 'image')
            ) {
                $fieldType = $inputType;
                $element = $fieldset->addField(
                    $attribute->getAttributeCode(),
                    $fieldType,
                    [
                        'name' => $attribute->getAttributeCode(),
                        'label' => __($attribute->getFrontendLabel()),
                        'class' => $attribute->getFrontendClass(),
                        'required' => $attribute->isRequired(),
                        'note' => $attribute->getNote()
                    ]
                );

                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));

                $this->_applyTypeSpecificConfigCustomer($inputType, $element, $attribute);
            }
        }
    }

    /**
     * Apply configuration specific for different element type
     *
     * @param string $inputType
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param AttributeMetadataInterface $attribute
     * @return void
     */
    protected function _applyTypeSpecificConfigCustomer(
        $inputType,
        $element,
        AttributeMetadataInterface $attribute
    ) {
        switch ($inputType) {
            case 'select':
                $element->setValues($this->_getAttributeOptionsArray($attribute));
                break;
            case 'multiselect':
                $element->setValues($this->_getAttributeOptionsArray($attribute));
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
     * @param AttributeMetadataInterface $attribute
     * @return array
     */
    protected function _getAttributeOptionsArray(AttributeMetadataInterface $attribute)
    {
        $options = $attribute->getOptions();
        $result = [];
        foreach ($options as $option) {
            $result[] = $this->dataObjectProcessor->buildOutputDataArray(
                $option,
                'Magento\Customer\Api\Data\OptionInterface'
            );
        }
        return $result;
    }
}
