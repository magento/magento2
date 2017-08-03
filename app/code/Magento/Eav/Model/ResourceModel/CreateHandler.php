<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel;

use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\AttributeInterface;
use Magento\Framework\Model\Entity\ScopeResolver;

/**
 * Class CreateHandler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.1.0
 */
class CreateHandler implements AttributeInterface
{
    /**
     * @var AttributeRepository
     * @since 2.1.0
     */
    private $attributeRepository;

    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    private $metadataPool;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.1.0
     */
    private $searchCriteriaBuilder;

    /**
     * @var AttributePersistor
     * @since 2.1.0
     */
    private $attributePersistor;

    /**
     * @var ScopeResolver
     * @since 2.1.0
     */
    private $scopeResolver;

    /**
     * @var AttributeLoader
     * @since 2.1.3
     */
    private $attributeLoader;

    /**
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributePersistor $attributePersistor
     * @param ScopeResolver $scopeResolver
     * @param AttributeLoader $attributeLoader
     * @since 2.1.0
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributePersistor $attributePersistor,
        ScopeResolver $scopeResolver,
        AttributeLoader $attributeLoader = null
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributePersistor = $attributePersistor;
        $this->scopeResolver = $scopeResolver;
        $this->attributeLoader = $attributeLoader ?: ObjectManager::getInstance()->get(AttributeLoader::class);
    }

    /**
     * @param string $entityType
     * @param int $attributeSetId
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     * @since 2.1.0
     */
    protected function getAttributes($entityType, $attributeSetId = null)
    {
        return $this->attributeLoader->getAttributes($entityType, $attributeSetId);
    }

    /**
     * @param string $entityType
     * @param array $entityData
     * @param array $arguments
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\ConfigurationMismatchException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function execute($entityType, $entityData, $arguments = [])
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        if ($metadata->getEavEntityType()) {
            $processed = [];
            $entityLinkField = $metadata->getLinkField();
            $attributeSetId = isset($entityData[AttributeLoader::ATTRIBUTE_SET_ID])
                ? $entityData[AttributeLoader::ATTRIBUTE_SET_ID]
                : null; // @todo verify is it normal to not have attributer_set_id
            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
            foreach ($this->getAttributes($entityType, $attributeSetId) as $attribute) {
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
