<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\OptionInterfaceFactory;
use Magento\Customer\Api\Data\ValidationRuleInterfaceFactory;

/**
 * Converter for AttributeMetadata
 */
class AttributeMetadataConverter
{
    /**
     * @var OptionInterfaceFactory
     */
    private $optionFactory;

    /**
     * @var ValidationRuleInterfaceFactory
     */
    private $validationRuleFactory;

    /**
     * @var AttributeMetadataDataBuilder
     */
    private $_attributeMetadataBuilder;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * Initialize the Converter
     *
     * @param OptionInterfaceFactory $optionFactory
     * @param ValidationRuleInterfaceFactory $validationRuleFactory
     * @param AttributeMetadataDataBuilder $attributeMetadataBuilder
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        OptionInterfaceFactory $optionFactory,
        ValidationRuleInterfaceFactory $validationRuleFactory,
        AttributeMetadataDataBuilder $attributeMetadataBuilder,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->optionFactory = $optionFactory;
        $this->validationRuleFactory = $validationRuleFactory;
        $this->_attributeMetadataBuilder = $attributeMetadataBuilder;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Create AttributeMetadata Data object from the Attribute Model
     *
     * @param \Magento\Customer\Model\Attribute $attribute
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface
     */
    public function createMetadataAttribute($attribute)
    {
        $options = [];
        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $option) {
                $optionDataObject = $this->optionFactory->create();
                if (!is_array($option['value'])) {
                    $optionDataObject->setValue($option['value']);
                } else {
                    $optionArray = [];
                    foreach ($option['value'] as $optionArrayValues) {
                        $optionObject = $this->optionFactory->create();
                        $this->dataObjectHelper->populateWithArray($optionObject, $optionArrayValues);
                        $optionArray[] = $optionObject;
                    }
                    $optionDataObject->setOptions($optionArray);
                }
                $optionDataObject->setLabel($option['label']);
                $options[] = $optionDataObject;
            }
        }
        $validationRules = [];
        foreach ($attribute->getValidateRules() as $name => $value) {
            $validationRule = $this->validationRuleFactory->create()
                ->setName($name)
                ->setValue($value);
            $validationRules[] = $validationRule;
        }

        $this->_attributeMetadataBuilder->setAttributeCode($attribute->getAttributeCode())
            ->setFrontendInput($attribute->getFrontendInput())
            ->setInputFilter((string)$attribute->getInputFilter())
            ->setStoreLabel($attribute->getStoreLabel())
            ->setValidationRules($validationRules)
            ->setVisible((boolean)$attribute->getIsVisible())
            ->setRequired((boolean)$attribute->getIsRequired())
            ->setMultilineCount((int)$attribute->getMultilineCount())
            ->setDataModel((string)$attribute->getDataModel())
            ->setOptions($options)
            ->setFrontendClass($attribute->getFrontend()->getClass())
            ->setFrontendLabel($attribute->getFrontendLabel())
            ->setNote((string)$attribute->getNote())
            ->setSystem((boolean)$attribute->getIsSystem())
            ->setUserDefined((boolean)$attribute->getIsUserDefined())
            ->setBackendType($attribute->getBackendType())
            ->setSortOrder((int)$attribute->getSortOrder());

        return $this->_attributeMetadataBuilder->create();
    }
}
