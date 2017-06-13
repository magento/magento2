<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Attribute;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\ResourceConnection;

/**
 * Class ScopeOverriddenValue
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ScopeOverriddenValue
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
     * @var array
     */
    private $attributesValues;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * ScopeOverriddenValue constructor.
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        ResourceConnection $resourceConnection
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Whether attribute value is overridden in specific store
     *
     * @param string $entityType
     * @param \Magento\Catalog\Model\AbstractModel $entity
     * @param string $attributeCode
     * @param int|string $storeId
     * @return bool
     */
    public function containsValue($entityType, $entity, $attributeCode, $storeId)
    {
        if ((int)$storeId === Store::DEFAULT_STORE_ID) {
            return false;
        }
        if ($this->attributesValues === null) {
            $this->initAttributeValues($entityType, $entity, (int)$storeId);
        }

        return isset($this->attributesValues[$storeId])
            && array_key_exists($attributeCode, $this->attributesValues[$storeId]);
    }

    /**
     * Get attribute default values
     *
     * @param string $entityType
     * @param \Magento\Catalog\Model\AbstractModel $entity
     * @return array
     *
     * @deprecated
     */
    public function getDefaultValues($entityType, $entity)
    {
        if ($this->attributesValues === null) {
            $this->initAttributeValues($entityType, $entity, (int)$entity->getStoreId());
        }

        return isset($this->attributesValues[Store::DEFAULT_STORE_ID])
            ? $this->attributesValues[Store::DEFAULT_STORE_ID]
            : [];
    }

    /**
     * @param string $entityType
     * @param \Magento\Catalog\Model\AbstractModel $entity
     * @param int $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function initAttributeValues($entityType, $entity, $storeId)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        $attributeTables = [];
        if ($metadata->getEavEntityType()) {
            foreach ($this->getAttributes($entityType) as $attribute) {
                if (!$attribute->isStatic()) {
                    $attributeTables[$attribute->getBackend()->getTable()][] = $attribute->getAttributeId();
                }
            }
            $storeIds = [Store::DEFAULT_STORE_ID];
            if ($storeId !== Store::DEFAULT_STORE_ID) {
                $storeIds[] = $storeId;
            }
            $selects = [];
            foreach ($attributeTables as $attributeTable => $attributeCodes) {
                $select = $metadata->getEntityConnection()->select()
                    ->from(['t' => $attributeTable], ['value' => 't.value', 'store_id' => 't.store_id'])
                    ->join(
                        ['a' => $this->resourceConnection->getTableName('eav_attribute')],
                        'a.attribute_id = t.attribute_id',
                        ['attribute_code' => 'a.attribute_code']
                    )
                    ->where($metadata->getLinkField() . ' = ?', $entity->getData($metadata->getLinkField()))
                    ->where('t.attribute_id IN (?)', $attributeCodes)
                    ->where('t.store_id IN (?)', $storeIds);
                $selects[] = $select;
            }

            $unionSelect = new \Magento\Framework\DB\Sql\UnionExpression(
                $selects,
                \Magento\Framework\DB\Select::SQL_UNION_ALL
            );
            $attributes = $metadata->getEntityConnection()->fetchAll((string)$unionSelect);
            foreach ($attributes as $attribute) {
                $this->attributesValues[$attribute['store_id']][$attribute['attribute_code']] = $attribute['value'];
            }
        }
    }

    /**
     * @param string $entityType
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     */
    private function getAttributes($entityType)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $searchResult = $this->attributeRepository->getList(
            $metadata->getEavEntityType(),
            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder
                        ->setField('is_global')
                        ->setConditionType('in')
                        ->setValue([ScopedAttributeInterface::SCOPE_STORE, ScopedAttributeInterface::SCOPE_WEBSITE])
                        ->create()
                ]
            )->create()
        );
        return $searchResult->getItems();
    }
}
