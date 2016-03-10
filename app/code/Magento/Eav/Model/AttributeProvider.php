<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\Framework\Model\EntitySnapshot\AttributeProviderInterface;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class EntitySnapshot
 */
class AttributeProvider implements AttributeProviderInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * AttributeProvider constructor.
     *
     * @param MetadataPool $metadataPool
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        MetadataPool $metadataPool,
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->metadataPool = $metadataPool;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Returns array of fields
     *
     * @param string $entityType
     * @return array
     * @throws \Exception
     */
    public function getAttributes($entityType)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $searchResult = $this->attributeRepository->getList(
            $metadata->getEavEntityType(),
            $this->searchCriteriaBuilder->create()
        );
        $attributes = [];
        foreach ($searchResult->getItems() as $attribute) {
            $attributes[] = $attribute->getAttributeCode();
        }
        return $attributes;
    }
}
