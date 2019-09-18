<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\TypeLocator;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;
use Magento\Framework\Webapi\ServiceTypeToEntityTypeMap;

/**
 * Class to find type based off of ServiceTypeToEntityTypeMap. This locator is introduced for backwards compatibility.
 * @deprecated 102.0.0
 */
class ServiceClassLocator implements CustomAttributeTypeLocatorInterface
{
    /**
     * @var ServiceTypeToEntityTypeMap
     */
    private $serviceTypeMap;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ComplexType
     */
    private $complexTypeLocator;

    /**
     * @var SimpleType
     */
    private $simpleTypeLocator;

    /**
     * @param ServiceTypeToEntityTypeMap $serviceTypeMap
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ComplexType $complexTypeLocator
     * @param SimpleType $simpleTypeLocator
     */
    public function __construct(
        ServiceTypeToEntityTypeMap $serviceTypeMap,
        AttributeRepositoryInterface $attributeRepository,
        ComplexType $complexTypeLocator,
        SimpleType $simpleTypeLocator
    ) {
        $this->serviceTypeMap = $serviceTypeMap;
        $this->attributeRepository = $attributeRepository;
        $this->complexTypeLocator = $complexTypeLocator;
        $this->simpleTypeLocator = $simpleTypeLocator;
    }

    /**
     * @inheritDoc
     */
    public function getType($attributeCode, $entityType)
    {
        $entityCode = $this->serviceTypeMap->getEntityType($entityType);
        if (!$entityCode) {
            return TypeProcessor::NORMALIZED_ANY_TYPE;
        }

        $type = $this->complexTypeLocator->getType($attributeCode, $entityCode);
        if ($type === TypeProcessor::NORMALIZED_ANY_TYPE) {
            $type = $this->simpleTypeLocator->getType($attributeCode, $entityCode);
        }

        return $type;
    }

    /**
     * @inheritDoc
     */
    public function getAllServiceDataInterfaces()
    {
        return $this->complexTypeLocator->getDataTypes();
    }
}
