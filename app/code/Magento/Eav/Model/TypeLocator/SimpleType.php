<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\TypeLocator;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\CustomAttribute\ServiceTypeListInterface;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;

/**
 * Class to locate simple types for Eav custom attributes
 */
class SimpleType implements CustomAttributeTypeLocatorInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ServiceTypeListInterface
     */
    private $serviceTypeList;

    /**
     * Constructor
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ServiceTypeListInterface $serviceTypeList
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ServiceTypeListInterface $serviceTypeList
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->serviceTypeList = $serviceTypeList;
    }

    /**
     * @inheritdoc
     */
    public function getType($attributeCode, $entityType)
    {
        try {
            $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        } catch (NoSuchEntityException $exception) {
            return TypeProcessor::NORMALIZED_ANY_TYPE;
        }
        $backendType = $attribute->getBackendType();
        $backendTypeMap = [
            'static' => TypeProcessor::NORMALIZED_ANY_TYPE,
            'int' => TypeProcessor::NORMALIZED_INT_TYPE,
            'text' => TypeProcessor::NORMALIZED_STRING_TYPE,
            'varchar' => TypeProcessor::NORMALIZED_STRING_TYPE,
            'datetime' => TypeProcessor::NORMALIZED_STRING_TYPE,
            'decimal' => TypeProcessor::NORMALIZED_DOUBLE_TYPE,
        ];
        return $backendTypeMap[$backendType] ?? TypeProcessor::NORMALIZED_ANY_TYPE;
    }

    /**
     * Get data Types from service type list
     *
     * @return void
     */
    public function getAllServiceDataInterfaces()
    {
        $this->serviceTypeList->getDataTypes();
    }
}
