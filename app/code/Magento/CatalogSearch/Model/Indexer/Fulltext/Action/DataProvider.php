<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;

/**
 * Catalog search full test search data provider.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @api
 * @since 100.0.3
 */
class DataProvider
{
    /**
     * Searchable attributes cache
     *
     * @var \Magento\Eav\Model\Entity\Attribute[]
     */
    private $searchableAttributes;

    /**
     * Index values separator
     *
     * @var string
     */
    private $separator = ' | ';

    /**
     * Product Type Instances cache
     *
     * @var array
     */
    private $productTypes = [];

    /**
     * Product Emulators cache
     *
     * @var array
     */
    private $productEmulators = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $productAttributeCollectionFactory;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * Catalog product type
     *
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $catalogProductType;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Engine
     */
    private $engine;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata
     */
    private $metadata;

    /**
     * @var array
     */
    private $attributeOptions = [];

    /**
     * Cache searchable attributes by backend type
     *
     * @var array
     */
    private $searchableAttributesByBackendType = [];

    /**
     * Adjusts a size of filtered rows for searchable products. Filtered rows counts by the following condition:
     * entity_id > X AND entity_id < X + BatchSize * antiGapMultiplier
     * It will help in case a lot of gaps between entity_id in product table, when selected amount of products will be
     * less than batch size
     *
     * @var int
     */
    private $antiGapMultiplier;

    /**
     * @param ResourceConnection $resource
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttributeCollectionFactory
     * @param \Magento\CatalogSearch\Model\ResourceModel\EngineProvider $engineProvider
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param int $antiGapMultiplier
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttributeCollectionFactory,
        \Magento\CatalogSearch\Model\ResourceModel\EngineProvider $engineProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        int $antiGapMultiplier = 5
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->catalogProductType = $catalogProductType;
        $this->eavConfig = $eavConfig;
        $this->productAttributeCollectionFactory = $prodAttributeCollectionFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->engine = $engineProvider->get();
        $this->metadata = $metadataPool->getMetadata(ProductInterface::class);
        $this->antiGapMultiplier = $antiGapMultiplier;
    }

    /**
     * Return validated table name
     *
     * @param string|string[] $table
     * @return string
     */
    private function getTable($table)
    {
        return $this->resource->getTableName($table);
    }

    /**
     * Retrieve searchable products per store
     *
     * @param int $storeId
     * @param array $staticFields
     * @param array|int $productIds
     * @param int $lastProductId
     * @param int $batch
     * @return array
     * @since 100.0.3
     */
    public function getSearchableProducts(
        $storeId,
        array $staticFields,
        $productIds = null,
        $lastProductId = 0,
        $batch = 100
    ) {

        $select = $this->getSelectForSearchableProducts($storeId, $staticFields, $productIds, $lastProductId, $batch);
        if ($productIds === null) {
            $select->where(
                'e.entity_id < ?',
                $lastProductId ? $this->antiGapMultiplier * $batch + $lastProductId + 1 : $batch + 1
            );
        }
        $products = $this->connection->fetchAll($select);
        if ($productIds === null && !$products) {
            // try to search without limit entity_id by batch size for cover case with a big gap between entity ids
            $products = $this->connection->fetchAll(
                $this->getSelectForSearchableProducts($storeId, $staticFields, $productIds, $lastProductId, $batch)
            );
        }

        return $products;
    }

    /**
     * Get Select object for searchable products
     *
     * @param int $storeId
     * @param array $staticFields
     * @param array|int $productIds
     * @param int $lastProductId
     * @param int $batch
     * @return Select
     */
    private function getSelectForSearchableProducts(
        $storeId,
        array $staticFields,
        $productIds,
        $lastProductId,
        $batch
    ) {
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        $lastProductId = (int) $lastProductId;

        $select = $this->connection->select()
            ->useStraightJoin(true)
            ->from(
                ['e' => $this->getTable('catalog_product_entity')],
                array_merge(['entity_id', 'type_id'], $staticFields)
            )
            ->join(
                ['website' => $this->getTable('catalog_product_website')],
                $this->connection->quoteInto('website.product_id = e.entity_id AND website.website_id = ?', $websiteId),
                []
            );

        $this->joinAttribute($select, 'visibility', $storeId, $this->engine->getAllowedVisibility());
        $this->joinAttribute($select, 'status', $storeId, [Status::STATUS_ENABLED]);

        if ($productIds !== null) {
            $select->where('e.entity_id IN (?)', $productIds);
        }
        $select->where('e.entity_id > ?', $lastProductId);
        $select->order('e.entity_id');
        $select->limit($batch);

        return $select;
    }

    /**
     * Join attribute to searchable product for filtration
     *
     * @param Select $select
     * @param string $attributeCode
     * @param int $storeId
     * @param array $whereValue
     */
    private function joinAttribute(Select $select, $attributeCode, $storeId, array $whereValue)
    {
        $linkField = $this->metadata->getLinkField();
        $attribute = $this->getSearchableAttribute($attributeCode);
        $attributeTable = $this->getTable('catalog_product_entity_' . $attribute->getBackendType());
        $defaultAlias = $attributeCode . '_default';
        $storeAlias = $attributeCode . '_store';

        $whereCondition = $this->connection->getCheckSql(
            $storeAlias . '.value_id > 0',
            $storeAlias . '.value',
            $defaultAlias . '.value'
        );

        $select->join(
            [$defaultAlias => $attributeTable],
            $this->connection->quoteInto(
                $defaultAlias . '.' . $linkField . '= e.' . $linkField . ' AND ' . $defaultAlias . '.attribute_id = ?',
                $attribute->getAttributeId()
            ) . $this->connection->quoteInto(
                ' AND ' . $defaultAlias . '.store_id = ?',
                Store::DEFAULT_STORE_ID
            ),
            []
        )->joinLeft(
            [$storeAlias => $attributeTable],
            $this->connection->quoteInto(
                $storeAlias . '.' . $linkField . '= e.' . $linkField . ' AND ' . $storeAlias . '.attribute_id = ?',
                $attribute->getAttributeId()
            ) . $this->connection->quoteInto(
                ' AND ' . $storeAlias . '.store_id = ?',
                $storeId
            ),
            []
        )->where(
            $whereCondition . ' IN (?)',
            $whereValue
        );
    }

    /**
     * Retrieve searchable attributes
     *
     * @param string $backendType
     * @return \Magento\Eav\Model\Entity\Attribute[]
     * @since 100.0.3
     */
    public function getSearchableAttributes($backendType = null)
    {
        if (null === $this->searchableAttributes) {
            $this->searchableAttributes = [];

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributes */
            $productAttributes = $this->productAttributeCollectionFactory->create();
            $productAttributes->addToIndexFilter(true);

            /** @var \Magento\Eav\Model\Entity\Attribute[] $attributes */
            $attributes = $productAttributes->getItems();

            /** @deprecated */
            $this->eventManager->dispatch(
                'catelogsearch_searchable_attributes_load_after',
                ['engine' => $this->engine, 'attributes' => $attributes]
            );
            
            $this->eventManager->dispatch(
                'catalogsearch_searchable_attributes_load_after',
                ['engine' => $this->engine, 'attributes' => $attributes]
            );

            $entity = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
                $this->searchableAttributes[$attribute->getAttributeId()] = $attribute;
                $this->searchableAttributes[$attribute->getAttributeCode()] = $attribute;
            }
        }

        if ($backendType !== null) {
            if (isset($this->searchableAttributesByBackendType[$backendType])) {
                return $this->searchableAttributesByBackendType[$backendType];
            }
            $this->searchableAttributesByBackendType[$backendType] = [];
            foreach ($this->searchableAttributes as $attribute) {
                if ($attribute->getBackendType() == $backendType) {
                    $this->searchableAttributesByBackendType[$backendType][$attribute->getAttributeId()] = $attribute;
                }
            }

            return $this->searchableAttributesByBackendType[$backendType];
        }

        return $this->searchableAttributes;
    }

    /**
     * Retrieve searchable attribute by Id or code
     *
     * @param int|string $attribute
     * @return \Magento\Eav\Model\Entity\Attribute
     * @since 100.0.3
     */
    public function getSearchableAttribute($attribute)
    {
        $attributes = $this->getSearchableAttributes();
        if (isset($attributes[$attribute])) {
            return $attributes[$attribute];
        }

        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attribute);
    }

    /**
     * Returns expression for field unification
     *
     * @param string $field
     * @param string $backendType
     * @return \Zend_Db_Expr
     */
    private function unifyField($field, $backendType = 'varchar')
    {
        if ($backendType == 'datetime') {
            $expr = $this->connection->getDateFormatSql($field, '%Y-%m-%d %H:%i:%s');
        } else {
            $expr = $field;
        }
        return $expr;
    }

    /**
     * Load product(s) attributes
     *
     * @param int $storeId
     * @param array $productIds
     * @param array $attributeTypes
     * @return array
     * @since 100.0.3
     */
    public function getProductAttributes($storeId, array $productIds, array $attributeTypes)
    {
        $result = [];
        $selects = [];
        $ifStoreValue = $this->connection->getCheckSql('t_store.value_id > 0', 't_store.value', 't_default.value');
        $linkField = $this->metadata->getLinkField();
        $productLinkFieldsToEntityIdMap = $this->connection->fetchPairs(
            $this->connection->select()->from(
                ['cpe' => $this->getTable('catalog_product_entity')],
                [$linkField, 'entity_id']
            )->where(
                'cpe.entity_id IN (?)',
                $productIds
            )
        );
        foreach ($attributeTypes as $backendType => $attributeIds) {
            if ($attributeIds) {
                $tableName = $this->getTable('catalog_product_entity_' . $backendType);

                $select = $this->connection->select()->from(
                    ['t' => $tableName],
                    [
                        $linkField => 't.' . $linkField,
                        'attribute_id' => 't.attribute_id',
                        'value' => $this->unifyField($ifStoreValue, $backendType),
                    ]
                )->joinLeft(
                    ['t_store' => $tableName],
                    $this->connection->quoteInto(
                        't.' . $linkField . '=t_store.' . $linkField .
                        ' AND t.attribute_id=t_store.attribute_id' .
                        ' AND t_store.store_id = ?',
                        $storeId
                    ),
                    []
                )->joinLeft(
                    ['t_default' => $tableName],
                    $this->connection->quoteInto(
                        't.' . $linkField . '=t_default.' . $linkField .
                        ' AND t.attribute_id=t_default.attribute_id' .
                        ' AND t_default.store_id = ?',
                        0
                    ),
                    []
                )->where(
                    't.attribute_id IN (?)',
                    $attributeIds
                )->where(
                    't.' . $linkField . ' IN (?)',
                    array_keys($productLinkFieldsToEntityIdMap)
                )->distinct();
                $selects[] = $select;
            }
        }

        if ($selects) {
            $select = $this->connection->select()->union($selects, Select::SQL_UNION_ALL);
            $query = $this->connection->query($select);
            while ($row = $query->fetch()) {
                $entityId = $productLinkFieldsToEntityIdMap[$row[$linkField]];
                $result[$entityId][$row['attribute_id']] = $row['value'];
            }
        }

        return $result;
    }

    /**
     * Retrieve Product Type Instance
     *
     * @param string $typeId
     * @return \Magento\Catalog\Model\Product\Type\AbstractType
     */
    private function getProductTypeInstance($typeId)
    {
        if (!isset($this->productTypes[$typeId])) {
            $productEmulator = $this->getProductEmulator($typeId);

            $this->productTypes[$typeId] = $this->catalogProductType->factory($productEmulator);
        }
        return $this->productTypes[$typeId];
    }

    /**
     * Return all product children ids
     *
     * @param int $productId Product Entity Id
     * @param string $typeId Super Product Link Type
     * @return array|null
     * @since 100.0.3
     */
    public function getProductChildIds($productId, $typeId)
    {
        $typeInstance = $this->getProductTypeInstance($typeId);
        $relation = $typeInstance->isComposite($this->getProductEmulator($typeId))
            ? $typeInstance->getRelationInfo()
            : false;

        if ($relation && $relation->getTable() && $relation->getParentFieldName() && $relation->getChildFieldName()) {
            $select = $this->connection->select()->from(
                ['main' => $this->getTable($relation->getTable())],
                [$relation->getChildFieldName()]
            );
            $select->join(
                ['e' => $this->resource->getTableName('catalog_product_entity')],
                'e.' . $this->metadata->getLinkField() . ' = main.' . $relation->getParentFieldName()
            )->where(
                'e.entity_id = ?',
                $productId
            );

            if ($relation->getWhere() !== null) {
                $select->where($relation->getWhere());
            }
            return $this->connection->fetchCol($select);
        }

        return null;
    }

    /**
     * Retrieve Product Emulator (Magento Object)
     *
     * @param string $typeId
     * @return \Magento\Framework\DataObject
     */
    private function getProductEmulator($typeId)
    {
        if (!isset($this->productEmulators[$typeId])) {
            $productEmulator = new \Magento\Framework\DataObject();
            $productEmulator->setTypeId($typeId);
            $this->productEmulators[$typeId] = $productEmulator;
        }
        return $this->productEmulators[$typeId];
    }

    /**
     * Prepare Fulltext index value for product
     *
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 100.0.3
     */
    public function prepareProductIndex($indexData, $productData, $storeId)
    {
        $index = [];

        foreach ($this->getSearchableAttributes('static') as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (isset($productData[$attributeCode])) {
                if ('store_id' === $attributeCode) {
                    continue;
                }

                $value = $this->getAttributeValue($attribute->getId(), $productData[$attributeCode], $storeId);
                if ($value) {
                    if (isset($index[$attribute->getId()])) {
                        if (!is_array($index[$attribute->getId()])) {
                            $index[$attribute->getId()] = [$index[$attribute->getId()]];
                        }
                        $index[$attribute->getId()][] = $value;
                    } else {
                        $index[$attribute->getId()] = $value;
                    }
                }
            }
        }
        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValues) {
                $value = $this->getAttributeValue($attributeId, $attributeValues, $storeId);
                if ($value !== null && $value !== false && $value !== '') {
                    if (!isset($index[$attributeId])) {
                        $index[$attributeId] = [];
                    }
                    $index[$attributeId][$entityId] = $value;
                }
            }
        }

        $product = $this->getProductEmulator(
            $productData['type_id']
        )->setId(
            $productData['entity_id']
        )->setStoreId(
            $storeId
        );
        $typeInstance = $this->getProductTypeInstance($productData['type_id']);
        $data = $typeInstance->getSearchableData($product);
        if ($data) {
            $index['options'] = $data;
        }

        return $this->engine->prepareEntityIndex($index, $this->separator);
    }

    /**
     * Retrieve attribute source value for search
     *
     * @param int $attributeId
     * @param mixed $valueIds
     * @param int $storeId
     * @return string
     */
    private function getAttributeValue($attributeId, $valueIds, $storeId)
    {
        $attribute = $this->getSearchableAttribute($attributeId);
        $value = $this->engine->processAttributeValue($attribute, $valueIds);
        if (false !== $value) {
            $optionValue = $this->getAttributeOptionValue($attributeId, $valueIds, $storeId);
            if (null === $optionValue) {
                $value = $this->filterAttributeValue($value);
            } else {
                $value = implode($this->separator, array_filter([$value, $optionValue]));
            }
        }

        return $value;
    }

    /**
     * Get attribute option value
     *
     * @param int $attributeId
     * @param int|string $valueIds
     * @param int $storeId
     * @return null|string
     */
    private function getAttributeOptionValue($attributeId, $valueIds, $storeId)
    {
        $optionKey = $attributeId . '-' . $storeId;
        $attributeValueIds = explode(',', $valueIds);
        $attributeOptionValue = '';
        if (!array_key_exists($optionKey, $this->attributeOptions)
        ) {
            $attribute = $this->getSearchableAttribute($attributeId);
            if ($this->engine->allowAdvancedIndex()
                && $attribute->getIsSearchable()
                && $attribute->usesSource()
            ) {
                $attribute->setStoreId($storeId);
                $options = $attribute->getSource()->toOptionArray();
                $this->attributeOptions[$optionKey] = array_column($options, 'label', 'value');
                $this->attributeOptions[$optionKey] = array_map(
                    function ($value) {
                        return $this->filterAttributeValue($value);
                    },
                    $this->attributeOptions[$optionKey]
                );
            } else {
                $this->attributeOptions[$optionKey] = null;
            }
        }
        foreach ($attributeValueIds as $attrValueId) {
            if (isset($this->attributeOptions[$optionKey][$attrValueId])) {
                $attributeOptionValue .= $this->attributeOptions[$optionKey][$attrValueId] . ' ';
            }
        }
        return empty($attributeOptionValue) ? null : trim($attributeOptionValue);
    }

    /**
     * Remove whitespaces and tags from attribute value
     *
     * @param string $value
     * @return string
     */
    private function filterAttributeValue($value)
    {
        return preg_replace('/\s+/iu', ' ', trim(strip_tags($value)));
    }
}
