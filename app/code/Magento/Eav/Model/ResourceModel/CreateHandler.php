<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel;

use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Eav\Model\Entity\AttributeCache;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\AttributeInterface;
use Magento\Framework\Model\Entity\ScopeResolver;

/**
 * Class CreateHandler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateHandler implements AttributeInterface
{
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
     * @var AttributePersistor
     */
    private $attributePersistor;

    /**
     * @var ScopeResolver
     */
    private $scopeResolver;

    /**
     * @var AttributeCache
     */
    private $attributeCache;

    /**
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributePersistor $attributePersistor
     * @param ScopeResolver $scopeResolver
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributePersistor $attributePersistor,
        ScopeResolver $scopeResolver
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributePersistor = $attributePersistor;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * @deprecated
     * @return AttributeCache
     */
    private function getAttributeCache()
    {
        if ($this->attributeCache === null) {
            $this->attributeCache = ObjectManager::getInstance()->get(AttributeCache::class);
        }

        return $this->attributeCache;
    }

    /**
     * @param string $entityType
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     * @throws \Exception
     */
    protected function getAttributes($entityType)
    {
        /** @var AttributeCache $cache */
        $cache = $this->getAttributeCache();
        if ($attributes = $cache->getAttributes($entityType)) {
            return $attributes;
        }

        $metadata = $this->metadataPool->getMetadata($entityType);

        $searchResult = $this->attributeRepository->getList(
            $metadata->getEavEntityType(),
            $this->searchCriteriaBuilder->addFilter('attribute_set_id', null, 'neq')->create()
        );
        return $searchResult->getItems();
    }

    /**
     * @param string $entityType
     * @param array $entityData
     * @param array $arguments
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\ConfigurationMismatchException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entityData, $arguments = [])
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        if ($metadata->getEavEntityType()) {
            $processed = [];
            $entityLinkField = $metadata->getLinkField();
            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
            foreach ($this->getAttributes($entityType) as $attribute) {
                if ($attribute->isStatic()) {
                    continue;
                }

                $attributeCode = $attribute->getAttributeCode();
                if (isset($entityData[$attributeCode])
                    && !is_array($entityData[$attributeCode])
                    && !$attribute->isValueEmpty($entityData[$attributeCode])
                ) {
                    $this->attributePersistor->registerInsert(
                        $entityType,
                        $entityData[$entityLinkField],
                        $attributeCode,
                        $entityData[$attributeCode]
                    );
                    $processed[$attributeCode] = $entityData[$attributeCode];
                }
            }
            $context = $this->scopeResolver->getEntityContext($entityType, $entityData);
            $this->attributePersistor->flush($entityType, $context);
        }
        return $entityData;
    }
}
