<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db;

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
     * @var Attribute[]
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
     * @var CollectionFactory
     */
    private $productAttributeCollectionFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var Type
     */
    private $catalogProductType;

    /**
     * Core event manager proxy
     *
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var EntityMetadata
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
     * @var GetSearchableProductsSelect|mixed
     */
    private $selectSearchableProducts;

    /**
     * @param ResourceConnection $resource
     * @param Type $catalogProductType
     * @param Config $eavConfig
     * @param CollectionFactory $prodAttributeCollectionFactory
     * @param EngineProvider $engineProvider
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param int $antiGapMultiplier
     * @param GetSearchableProductsSelect|null $getSearchableProductsSelect
     * @throws Exception
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceConnection $resource,
        Type $catalogProductType,
        Config $eavConfig,
        CollectionFactory $prodAttributeCollectionFactory,
        EngineProvider $engineProvider,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        int $antiGapMultiplier = 5,
        GetSearchableProductsSelect $getSearchableProductsSelect = null
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
        $this->selectSearchableProducts = $getSearchableProductsSelect ?:
            ObjectManager::getInstance()->get(GetSearchableProductsSelect::class);
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
        $batch = 1000
    ) {
        $select = $this->selectSearchableProducts->execute(
            (int) $storeId,
            $staticFields,
            $productIds,
            $lastProductId,
            $batch
        );
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
                $this->selectSearchableProducts->execute(
                    (int) $storeId,
                    $staticFields,
                    $productIds,
                    $lastProductId,
                    $batch
                )
            );
        }

        return $products;
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
        /** TODO: Remove this block in the next minor release and add a new public method instead */
        if ($this->eavConfig->getEntityType(Product::ENTITY)->getNeedRefreshSearchAttributesList()) {
            $this->clearSearchableAttributesList();
        }
        if (null === $this->searchableAttributes) {
            $this->searchableAttributes = [];

            $productAttributes = $this->productAttributeCollectionFactory->create();
            $productAttributes->addToIndexFilter(true);

            /** @var Attribute[] $attributes */
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

            $entity = $this->eavConfig->getEntityType(Product::ENTITY)->getEntity();

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
     * Remove searchable attributes list.
     *
     * @return void
     */
    private function clearSearchableAttributesList(): void
    {
        $this->searchableAttributes = null;
        $this->searchableAttributesByBackendType = [];
        $this->eavConfig->getEntityType(Product::ENTITY)->unsNeedRefreshSearchAttributesList();
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

        return $this->eavConfig->getAttribute(Product::ENTITY, $attribute);
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
                $productIds,
                Zend_Db::INT_TYPE
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
            $productEmulator = new DataObject();
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
     *
     * @return null|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getAttributeOptionValue($attributeId, $valueIds, $storeId)
    {
        $optionKey = $attributeId . '-' . $storeId;
        $attributeValueIds = $valueIds !== null ? explode(',', $valueIds) : [];
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
     * @param string|null $value
     * @return string
     */
    private function filterAttributeValue(?string $value)
    {
        return $value !== null ? preg_replace('/\s+/iu', ' ', trim(strip_tags($value))) : '';
    }
}
