<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel;

use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Eav\Model\Entity\AttributeCache;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Сlass responsible for loading and caching of attributes related to the given attribute set.
 *
 * Can be used to improve performance of services that mostly read attribute data.
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
     * AttributeLoader constructor.
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeCache $attributeCache
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeCache $attributeCache
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
