<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Attribute;

use Magento\Catalog\Model\AbstractModel;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\ResourceConnection;

/**
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
     * @var FilterBuilder
     */
    private $filterBuilder;

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
     * @param AbstractModel $entity
     * @param string $attributeCode
     * @param int|string $storeId
     * @return bool
     */
    public function containsValue($entityType, $entity, $attributeCode, $storeId)
    {
        if ((int)$storeId === Store::DEFAULT_STORE_ID) {
            return false;
        }
        $values = $this->getAttributesValues($entityType, $entity);

        if (!isset($values[$storeId])) {
            $this->initAttributeValues($entityType, $entity, (int)$storeId);
            $values = $this->getAttributesValues($entityType, $entity);
        }

        return isset($values[$storeId]) && array_key_exists($attributeCode, $values[$storeId]);
    }

    /**
     * Get attribute default values
     *
     * @param string $entityType
     * @param AbstractModel $entity
     * @return array
     *
     * @deprecated 101.0.0
     */
    public function getDefaultValues($entityType, $entity)
    {
        $values = $this->getAttributesValues($entityType, $entity);
        if (!isset($values[Store::DEFAULT_STORE_ID])) {
            $this->initAttributeValues($entityType, $entity, (int)$entity->getStoreId());
            $values = $this->getAttributesValues($entityType, $entity);
        }

        return $values[Store::DEFAULT_STORE_ID] ?? [];
    }

    /**
     * Init attribute values.
     *
     * @param string $entityType
     * @param AbstractModel $entity
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
            $entityId = $entity->getData($metadata->getLinkField());
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
                    ->where($metadata->getLinkField() . ' = ?', $entityId)
                    ->where('t.attribute_id IN (?)', $attributeCodes)
                    ->where('t.store_id IN (?)', $storeIds);
                $selects[] = $select;
            }

            $unionSelect = new \Magento\Framework\DB\Sql\UnionExpression(
                $selects,
                \Magento\Framework\DB\Select::SQL_UNION_ALL
            );
            $attributes = $metadata->getEntityConnection()->fetchAll((string)$unionSelect);
            $values = array_fill_keys($storeIds, []);
            foreach ($attributes as $attribute) {
                $values[$attribute['store_id']][$attribute['attribute_code']] = $attribute['value'];
            }
            $values += $this->getAttributesValues($entityType, $entity);
            $this->setAttributesValues($entityType, $entity, $values);
        }
    }

    /**
     * Returns entity attributes.
     *
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

    /**
     * Clear entity attributes values cache
     *
     * @param string $entityType
     * @param DataObject $entity
     * @return void
     * @throws \Exception
     */
    public function clearAttributesValues(string $entityType, DataObject $entity): void
    {
        if (isset($this->attributesValues[$entityType])) {
            $metadata = $this->metadataPool->getMetadata($entityType);
            $entityId = $entity->getData($metadata->getLinkField());
            unset($this->attributesValues[$entityType][$entityId]);
        }
    }

    /**
     * Get entity attributes values from cache
     *
     * @param string $entityType
     * @param DataObject $entity
     * @return array
     * @throws \Exception
     */
    private function getAttributesValues(string $entityType, DataObject $entity): array
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $entityId = $entity->getData($metadata->getLinkField());
        return $this->attributesValues[$entityType][$entityId] ?? [];
    }

    /**
     * Set entity attributes values into cache
     *
     * @param string $entityType
     * @param DataObject $entity
     * @param array $values
     * @return void
     * @throws \Exception
     */
    private function setAttributesValues(string $entityType, DataObject $entity, array $values): void
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $entityId = $entity->getData($metadata->getLinkField());
        $this->attributesValues[$entityType][$entityId] = $values;
    }
}
