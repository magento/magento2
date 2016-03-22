<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Model\Entity\ScopeResolver;

/**
 * Class UpdateHandler
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateHandler
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
     * @var ReadSnapshot
     */
    private $readSnapshot;

    /**
     * @var ScopeResolver
     */
    private $scopeResolver;

    /**
     * UpdateHandler constructor.
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributePersistor $attributePersistor
     * @param ReadSnapshot $readSnapshot
     * @param ScopeResolver $scopeResolver
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributePersistor $attributePersistor,
        ReadSnapshot $readSnapshot,
        ScopeResolver $scopeResolver
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributePersistor = $attributePersistor;
        $this->readSnapshot = $readSnapshot;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * @param string $entityType
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     * @throws \Exception
     */
    protected function getAttributes($entityType)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);

        $searchResult = $this->attributeRepository->getList(
            $metadata->getEavEntityType(),
            $this->searchCriteriaBuilder->addFilter('attribute_set_id', null, 'neq')->create()
        );
        return $searchResult->getItems();
    }

    /**
     * @param string $entityType
     * @param array $data
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute($entityType, $data)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        $metadata = $this->metadataPool->getMetadata($entityType);
        if ($metadata->getEavEntityType()) {
            $snapshot = $this->readSnapshot->execute($entityType, $data);
            $processed = [];
            foreach ($this->getAttributes($entityType) as $attribute) {
                if ($attribute->isStatic()) {
                    continue;
                }
                /**
                 * Only scalar values can be stored in generic tables
                 */
                if (isset($data[$attribute->getAttributeCode()]) && !is_scalar($data[$attribute->getAttributeCode()])) {
                    continue;
                }
                if (isset($snapshot[$attribute->getAttributeCode()])
                    && $snapshot[$attribute->getAttributeCode()] !== false
                    && (array_key_exists($attribute->getAttributeCode(), $data)
                        && $attribute->isValueEmpty($data[$attribute->getAttributeCode()]))
                ) {
                    $this->attributePersistor->registerDelete(
                        $entityType,
                        $data[$metadata->getLinkField()],
                        $attribute->getAttributeCode()
                    );
                }
                if ((!array_key_exists($attribute->getAttributeCode(), $snapshot)
                        || $snapshot[$attribute->getAttributeCode()] === false)
                    && array_key_exists($attribute->getAttributeCode(), $data)
                    && !$attribute->isValueEmpty($data[$attribute->getAttributeCode()])
                ) {
                    $this->attributePersistor->registerInsert(
                        $entityType,
                        $data[$metadata->getLinkField()],
                        $attribute->getAttributeCode(),
                        $data[$attribute->getAttributeCode()]
                    );
                    $processed[$attribute->getAttributeCode()] = $data[$attribute->getAttributeCode()];
                }
                if (array_key_exists($attribute->getAttributeCode(), $snapshot)
                    && $snapshot[$attribute->getAttributeCode()] !== false
                    && array_key_exists($attribute->getAttributeCode(), $data)
                    && $snapshot[$attribute->getAttributeCode()] != $data[$attribute->getAttributeCode()]
                    && !$attribute->isValueEmpty($data[$attribute->getAttributeCode()])
                ) {
                    $this->attributePersistor->registerUpdate(
                        $entityType,
                        $data[$metadata->getLinkField()],
                        $attribute->getAttributeCode(),
                        $data[$attribute->getAttributeCode()]
                    );
                    $processed[$attribute->getAttributeCode()] = $data[$attribute->getAttributeCode()];
                }
            }
            $context = $this->scopeResolver->getEntityContext($entityType, $data);
            $this->attributePersistor->flush($entityType, $context);
        }
        return $data;
    }
}
