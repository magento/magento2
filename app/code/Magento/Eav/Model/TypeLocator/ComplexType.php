<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\TypeLocator;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;
use Magento\Framework\Webapi\CustomAttribute\ServiceTypeListInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class to locate complex types for EAV custom attributes
 */
class ComplexType implements ServiceTypeListInterface, CustomAttributeTypeLocatorInterface
{
    /**
     * @var StringUtils
     */
    private $stringUtility;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var array
     */
    private $backendModelToAttributeTypeMap;

    /**
     * Initialize dependencies
     *
     * @codeCoverageIgnore
     * @param StringUtils $stringUtility
     * @param AttributeRepositoryInterface $attributeRepository
     * @param array $backendModelToAttributeTypeMap
     */
    public function __construct(
        StringUtils $stringUtility,
        AttributeRepositoryInterface $attributeRepository,
        array $backendModelToAttributeTypeMap
    ) {
        $this->stringUtility = $stringUtility;
        $this->attributeRepository = $attributeRepository;
        $this->backendModelToAttributeTypeMap = $backendModelToAttributeTypeMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($attributeCode, $entityType)
    {
        try {
            $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        } catch (NoSuchEntityException $exception) {
            return TypeProcessor::NORMALIZED_ANY_TYPE;
        }
        $backendModel = $attribute->getBackendModel();
        $attributeTypeMap = $this->getAttributeBackendModelToTypeMapping();
        //If empty backend model, check if it can be derived
        if (empty($backendModel)
            && ($attribute->getBackendType() == 'static'
                || $attribute->getFrontendInput() === 'image')
        ) {
            $backendModelClass = sprintf(
                'Magento\Eav\Model\Attribute\Data\%s',
                $this->stringUtility->upperCaseWords($attribute->getFrontendInput())
            );
            $backendModel = class_exists($backendModelClass) ? $backendModelClass : null;
        }

        $dataInterface = isset($attributeTypeMap[$backendModel])
            ? $attributeTypeMap[$backendModel]
            : TypeProcessor::NORMALIZED_ANY_TYPE;

        return $dataInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTypes()
    {
        $dataInterfaceArray = [];
        foreach ($this->getAttributeBackendModelToTypeMapping() as $attributeType) {
            if (interface_exists($attributeType)) {
                $dataInterfaceArray[] = $attributeType;
            }
        }
        return array_unique($dataInterfaceArray);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllServiceDataInterfaces()
    {
        return $this->getDataTypes();
    }

    /**
     * @return array [['backend model' => 'simple or complex type'], ..]
     */
    private function getAttributeBackendModelToTypeMapping()
    {
        $attributeTypeMap = [];
        foreach ($this->backendModelToAttributeTypeMap as $key => $value) {
            if (is_array($value)) {
                // Alternative declaration format is supported for backward compatibility
                foreach ($value as $backendModel => $attributeType) {
                    $attributeTypeMap[$backendModel] = $attributeType;
                }
            } else {
                $attributeTypeMap[$key] = $value;
            }
        }
        return $attributeTypeMap;
    }
}
