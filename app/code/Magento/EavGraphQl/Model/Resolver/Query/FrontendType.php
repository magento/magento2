<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver\Query;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\ServiceTypeToEntityTypeMap;

/**
 * Get frontend input type for EAV attribute
 */
class FrontendType
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ServiceTypeToEntityTypeMap
     */
    private $serviceTypeMap;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ServiceTypeToEntityTypeMap $serviceTypeMap
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ServiceTypeToEntityTypeMap $serviceTypeMap
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->serviceTypeMap = $serviceTypeMap;
    }

    /**
     * Return frontend type for attribute
     *
     * @param string $attributeCode
     * @param string $entityType
     * @return null|string
     */
    public function getType(string $attributeCode, string $entityType): ?string
    {
        $mappedEntityType = $this->serviceTypeMap->getEntityType($entityType);
        if ($mappedEntityType) {
            $entityType = $mappedEntityType;
        }
        try {
            $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $attribute->getFrontendInput();
    }
}
