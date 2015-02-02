<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\OptionDataBuilder;
use Magento\Customer\Api\Data\ValidationRuleDataBuilder;

/**
 * Converter for AttributeMetadata
 */
class AttributeMetadataConverter
{
    /**
     * @var OptionDataBuilder
     */
    private $_optionBuilder;

    /**
     * @var ValidationRuleDataBuilder
     */
    private $_validationRuleBuilder;

    /**
     * @var AttributeMetadataDataBuilder
     */
    private $_attributeMetadataBuilder;

    /**
     * Initialize the Converter
     *
     * @param OptionDataBuilder $optionBuilder
     * @param ValidationRuleDataBuilder $validationRuleBuilder
     * @param AttributeMetadataDataBuilder $attributeMetadataBuilder
     */
    public function __construct(
        OptionDataBuilder $optionBuilder,
        ValidationRuleDataBuilder $validationRuleBuilder,
        AttributeMetadataDataBuilder $attributeMetadataBuilder
    ) {
        $this->_optionBuilder = $optionBuilder;
        $this->_validationRuleBuilder = $validationRuleBuilder;
        $this->_attributeMetadataBuilder = $attributeMetadataBuilder;
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
                if (!is_array($option['value'])) {
                    $this->_optionBuilder->setValue($option['value']);
                } else {
                    $optionArray = [];
                    foreach ($option['value'] as $optionArrayValues) {
                        $optionArray[] = $this->_optionBuilder->populateWithArray($optionArrayValues)->create();
                    }
                    $this->_optionBuilder->setOptions($optionArray);
                }
                $this->_optionBuilder->setLabel($option['label']);
                $options[] = $this->_optionBuilder->create();
            }
        }
        $validationRules = [];
        foreach ($attribute->getValidateRules() as $name => $value) {
            $validationRules[] = $this->_validationRuleBuilder->setName($name)
                ->setValue($value)
                ->create();
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
