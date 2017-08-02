<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\Framework\Model\EntitySnapshot\AttributeProviderInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class EntitySnapshot
 * @since 2.1.0
 */
class AttributeProvider implements AttributeProviderInterface
{
    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @var AttributeRepositoryInterface
     * @since 2.1.0
     */
    protected $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.1.0
     */
    protected $searchCriteriaBuilder;

    /**
     * AttributeProvider constructor.
     *
     * @param MetadataPool $metadataPool
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function getAttributes($entityType)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $searchResult = $this->attributeRepository->getList(
            $metadata->getEavEntityType(),
            $this->searchCriteriaBuilder->addFilter('attribute_set_id', null, 'neq')->create()
        );
        $attributes = [];
        foreach ($searchResult->getItems() as $attribute) {
            $attributes[] = $attribute->getAttributeCode();
        }
        return $attributes;
    }
}
