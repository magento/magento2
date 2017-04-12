<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Api\Data\OptionInterfaceFactory;
use Magento\Customer\Api\Data\ValidationRuleInterface;
use Magento\Customer\Api\Data\ValidationRuleInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Hydrator for AttributeMetadataInterface
 */
class AttributeMetadataHydrator
{
    /**
     * @var AttributeMetadataInterfaceFactory
     */
    private $attributeMetadataFactory;

    /**
     * @var OptionInterfaceFactory
     */
    private $optionFactory;

    /**
     * @var ValidationRuleInterfaceFactory
     */
    private $validationRuleFactory;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * Constructor
     *
     * @param AttributeMetadataInterfaceFactory $attributeMetadataFactory
     * @param OptionInterfaceFactory $optionFactory
     * @param ValidationRuleInterfaceFactory $validationRuleFactory
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        AttributeMetadataInterfaceFactory $attributeMetadataFactory,
        OptionInterfaceFactory $optionFactory,
        ValidationRuleInterfaceFactory $validationRuleFactory,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->attributeMetadataFactory = $attributeMetadataFactory;
        $this->optionFactory = $optionFactory;
        $this->validationRuleFactory = $validationRuleFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Convert array to AttributeMetadataInterface
     *
     * @param array $data
     * @return AttributeMetadataInterface
     */
    public function hydrate(array $data)
    {
        if (isset($data[AttributeMetadataInterface::OPTIONS])) {
            $data[AttributeMetadataInterface::OPTIONS] = $this->createOptions(
                $data[AttributeMetadataInterface::OPTIONS]
            );
        }
        if (isset($data[AttributeMetadataInterface::VALIDATION_RULES])) {
            $data[AttributeMetadataInterface::VALIDATION_RULES] = $this->createValidationRules(
                $data[AttributeMetadataInterface::VALIDATION_RULES]
            );
        }
        return $this->attributeMetadataFactory->create(['data' => $data]);
    }

    /**
     * Populate options with data
     *
     * @param array $data
     * @return OptionInterface[]
     */
    private function createOptions(array $data)
    {
        foreach ($data as $key => $optionData) {
            if (isset($optionData[OptionInterface::OPTIONS])) {
                $optionData[OptionInterface::OPTIONS] = $this->createOptions($optionData[OptionInterface::OPTIONS]);
            }
            $data[$key] = $this->optionFactory->create(['data' => $optionData]);
        }
        return $data;
    }

    /**
     * Populate validation rules with data
     *
     * @param array $data
     * @return ValidationRuleInterface[]
     */
    private function createValidationRules(array $data)
    {
        foreach ($data as $key => $validationRuleData) {
            $data[$key] = $this->validationRuleFactory->create(['data' => $validationRuleData]);
        }
        return $data;
    }

    /**
     * Convert AttributeMetadataInterface to array
     *
     * @param AttributeMetadataInterface $attributeMetadata
     * @return array
     */
    public function extract($attributeMetadata)
    {
        return $this->dataObjectProcessor->buildOutputDataArray(
            $attributeMetadata,
            AttributeMetadataInterface::class
        );
    }
}
