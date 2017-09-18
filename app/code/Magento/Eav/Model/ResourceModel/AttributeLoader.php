<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel;

use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Loads attributes by attribute set
 */
class AttributeLoader
{
    /** Name of ATTRIBUTE_SET_ID field */
    const ATTRIBUTE_SET_ID = 'attribute_set_id';

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Constructor
     *
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get attributes list from attribute set
     *
     * @param string $entityType
     * @param int|null $attributeSetId
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     */
    public function getAttributes($entityType, $attributeSetId = null)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);

        if ($attributeSetId === null) {
            $criteria = $this->searchCriteriaBuilder->addFilter(self::ATTRIBUTE_SET_ID, null, 'neq')->create();
        } else {
            $criteria = $this->searchCriteriaBuilder->addFilter(self::ATTRIBUTE_SET_ID, $attributeSetId)->create();
        }

        $searchResult = $this->attributeRepository->getList(
            $metadata->getEavEntityType(),
            $criteria
        );
        $attributes = $searchResult->getItems();

        return $attributes;
    }
}
