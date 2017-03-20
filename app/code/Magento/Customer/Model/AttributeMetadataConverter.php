<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\OptionInterfaceFactory;
use Magento\Customer\Api\Data\ValidationRuleInterfaceFactory;
use Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory;
use Magento\Eav\Api\Data\AttributeDefaultValueInterface;

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
     * @var AttributeMetadataInterfaceFactory
     */
    private $attributeMetadataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Initialize the Converter
     *
     * @param OptionInterfaceFactory $optionFactory
     * @param ValidationRuleInterfaceFactory $validationRuleFactory
     * @param AttributeMetadataInterfaceFactory $attributeMetadataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        OptionInterfaceFactory $optionFactory,
        ValidationRuleInterfaceFactory $validationRuleFactory,
        AttributeMetadataInterfaceFactory $attributeMetadataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->optionFactory = $optionFactory;
        $this->validationRuleFactory = $validationRuleFactory;
        $this->attributeMetadataFactory = $attributeMetadataFactory;
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
                        $this->dataObjectHelper->populateWithArray(
                            $optionObject,
                            $optionArrayValues,
                            \Magento\Customer\Api\Data\OptionInterface::class
                        );
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

        $attributeMetaData = $this->attributeMetadataFactory->create();

        if ($attributeMetaData instanceof AttributeDefaultValueInterface) {
            $attributeMetaData->setDefaultValue($attribute->getDefaultValue());
        }

        return $attributeMetaData->setAttributeCode($attribute->getAttributeCode())
            ->setFrontendInput($attribute->getFrontendInput())
            ->setInputFilter((string)$attribute->getInputFilter())
            ->setStoreLabel($attribute->getStoreLabel())
            ->setValidationRules($validationRules)
            ->setIsVisible((boolean)$attribute->getIsVisible())
            ->setIsRequired((boolean)$attribute->getIsRequired())
            ->setMultilineCount((int)$attribute->getMultilineCount())
            ->setDataModel((string)$attribute->getDataModel())
            ->setOptions($options)
            ->setFrontendClass($attribute->getFrontend()->getClass())
            ->setFrontendLabel($attribute->getFrontendLabel())
            ->setNote((string)$attribute->getNote())
            ->setIsSystem((boolean)$attribute->getIsSystem())
            ->setIsUserDefined((boolean)$attribute->getIsUserDefined())
            ->setBackendType($attribute->getBackendType())
            ->setSortOrder((int)$attribute->getSortOrder())
            ->setIsUsedInGrid($attribute->getIsUsedInGrid())
            ->setIsVisibleInGrid($attribute->getIsVisibleInGrid())
            ->setIsFilterableInGrid($attribute->getIsFilterableInGrid())
            ->setIsSearchableInGrid($attribute->getIsSearchableInGrid());
    }
}
