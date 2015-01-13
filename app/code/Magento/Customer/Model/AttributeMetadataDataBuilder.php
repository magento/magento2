<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Framework\Api\AttributeMetadataBuilderInterface;

/**
 * DataBuilder class for \Magento\Customer\Api\Data\AttributeMetadataInterface
 */
class AttributeMetadataDataBuilder extends \Magento\Framework\Api\Builder implements AttributeMetadataBuilderInterface
{
    /**
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode)
    {
        $this->_set('attribute_code', $attributeCode);
        return $this;
    }

    /**
     * @param string $frontendInput
     * @return $this
     */
    public function setFrontendInput($frontendInput)
    {
        $this->_set('frontend_input', $frontendInput);
        return $this;
    }

    /**
     * @param string $inputFilter
     * @return $this
     */
    public function setInputFilter($inputFilter)
    {
        $this->_set('input_filter', $inputFilter);
        return $this;
    }

    /**
     * @param string $storeLabel
     * @return $this
     */
    public function setStoreLabel($storeLabel)
    {
        $this->_set('store_label', $storeLabel);
        return $this;
    }

    /**
     * @param \Magento\Customer\Api\Data\ValidationRuleInterface[] $validationRules
     * @return $this
     */
    public function setValidationRules($validationRules)
    {
        $this->_set('validation_rules', $validationRules);
        return $this;
    }

    /**
     * @param int $multilineCount
     * @return $this
     */
    public function setMultilineCount($multilineCount)
    {
        $this->_set('multiline_count', $multilineCount);
        return $this;
    }

    /**
     * @param bool $visible
     * @return $this
     */
    public function setVisible($visible)
    {
        $this->_set('visible', $visible);
        return $this;
    }

    /**
     * @param bool $required
     * @return $this
     */
    public function setRequired($required)
    {
        $this->_set('required', $required);
        return $this;
    }

    /**
     * @param string $dataModel
     * @return $this
     */
    public function setDataModel($dataModel)
    {
        $this->_set('data_model', $dataModel);
        return $this;
    }

    /**
     * @param \Magento\Customer\Api\Data\OptionInterface[] $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->_set('options', $options);
        return $this;
    }

    /**
     * @param string $frontendClass
     * @return $this
     */
    public function setFrontendClass($frontendClass)
    {
        $this->_set('frontend_class', $frontendClass);
        return $this;
    }

    /**
     * @param bool $userDefined
     * @return $this
     */
    public function setUserDefined($userDefined)
    {
        $this->_set('user_defined', $userDefined);
        return $this;
    }

    /**
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        $this->_set('sort_order', $sortOrder);
        return $this;
    }

    /**
     * @param string $frontendLabel
     * @return $this
     */
    public function setFrontendLabel($frontendLabel)
    {
        $this->_set('frontend_label', $frontendLabel);
        return $this;
    }

    /**
     * @param string $note
     * @return $this
     */
    public function setNote($note)
    {
        $this->_set('note', $note);
        return $this;
    }

    /**
     * @param bool $system
     * @return $this
     */
    public function setSystem($system)
    {
        $this->_set('system', $system);
        return $this;
    }

    /**
     * @param string $backendType
     * @return $this
     */
    public function setBackendType($backendType)
    {
        $this->_set('backend_type', $backendType);
        return $this;
    }

    /**
     * Initialize the builder
     *
     * @param \Magento\Framework\Api\ObjectFactory $objectFactory
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory
     * @param \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig
     * @param string|null $modelClassInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\Api\ObjectFactory $objectFactory,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory,
        \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig,
        $modelClassInterface = null
    ) {
        parent::__construct(
            $objectFactory,
            $metadataService,
            $attributeValueBuilder,
            $objectProcessor,
            $typeProcessor,
            $dataBuilderFactory,
            $objectManagerConfig,
            'Magento\Customer\Api\Data\AttributeMetadataInterface'
        );
    }
}
