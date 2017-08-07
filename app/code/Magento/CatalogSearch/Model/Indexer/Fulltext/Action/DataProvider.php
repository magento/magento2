<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @param ResourceConnection $resource
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttributeCollectionFactory
     * @param \Magento\CatalogSearch\Model\ResourceModel\EngineProvider $engineProvider
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @since 100.0.3
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttributeCollectionFactory,
        \Magento\CatalogSearch\Model\ResourceModel\EngineProvider $engineProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
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
     * @param int $limit
     * @return array
     * @since 100.0.3
     */
    public function getSearchableProducts(
        $storeId,
        array $staticFields,
        $productIds = null,
        $lastProductId = 0,
        $limit = 100
    ) {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
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

        if ($productIds !== null) {
            $select->where('e.entity_id IN (?)', $productIds);
        }

        $select->where('e.entity_id > ?', $lastProductId)->limit($limit)->order('e.entity_id');

        $result = $this->connection->fetchAll($select);

        return $result;
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

            $this->eventManager->dispatch(
                'catelogsearch_searchable_attributes_load_after',
                ['engine' => $this->engine, 'attributes' => $attributes]
            );

            $entity = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
            }

            $this->searchableAttributes = $attributes;
        }

        if ($backendType !== null) {
            $attributes = [];
            foreach ($this->searchableAttributes as $attributeId => $attribute) {
                if ($attribute->getBackendType() == $backendType) {
                    $attributes[$attributeId] = $attribute;
                }
            }

            return $attributes;
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
        if (is_numeric($attribute)) {
            if (isset($attributes[$attribute])) {
                return $attributes[$attribute];
            }
        } elseif (is_string($attribute)) {
            foreach ($attributes as $attributeModel) {
                if ($attributeModel->getAttributeCode() == $attribute) {
                    return $attributeModel;
                }
            }
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
                $selects[] = $this->connection->select()->from(
                    ['t_default' => $tableName],
                    [$linkField, 'attribute_id']
                )->joinLeft(
                    ['t_store' => $tableName],
                    $this->connection->quoteInto(
                        't_default.' . $linkField . '=t_store.' . $linkField .
                        ' AND t_default.attribute_id=t_store.attribute_id' .
                        ' AND t_store.store_id = ?',
                        $storeId
                    ),
                    ['value' => $this->unifyField($ifStoreValue, $backendType)]
                )->where(
                    't_default.store_id = ?',
                    0
                )->where(
                    't_default.attribute_id IN (?)',
                    $attributeIds
                )->where(
                    't_default.' . $linkField . ' IN (?)',
                    array_keys($productLinkFieldsToEntityIdMap)
                );
            }
        }

        if ($selects) {
            $select = $this->connection->select()->union($selects, \Magento\Framework\DB\Select::SQL_UNION_ALL);
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
        $relation = $typeInstance->isComposite(
            $this->getProductEmulator($typeId)
        ) ? $typeInstance->getRelationInfo() : false;

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
     * @return string
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
            foreach ($attributeData as $attributeId => $attributeValue) {
                $value = $this->getAttributeValue($attributeId, $attributeValue, $storeId);
                if (!empty($value)) {
                    if (isset($index[$attributeId])) {
                        $index[$attributeId][$entityId] = $value;
                    } else {
                        $index[$attributeId] = [$entityId => $value];
                    }
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
     * @param mixed $valueId
     * @param int $storeId
     * @return string
     */
    private function getAttributeValue($attributeId, $valueId, $storeId)
    {
        $attribute = $this->getSearchableAttribute($attributeId);
        $value = $this->engine->processAttributeValue($attribute, $valueId);

        if (false !== $value
            && $attribute->getIsSearchable()
            && $attribute->usesSource()
            && $this->engine->allowAdvancedIndex()
        ) {
            if (!isset($this->attributeOptions[$attributeId][$storeId])) {
                $attribute->setStoreId($storeId);
                $options = $attribute->getSource()->toOptionArray();
                $this->attributeOptions[$attributeId][$storeId] = array_combine(
                    array_column($options, 'value'),
                    array_column($options, 'label')
                );
            }

            $valueText = '';
            if (isset($this->attributeOptions[$attributeId][$storeId][$valueId])) {
                $valueText = $this->attributeOptions[$attributeId][$storeId][$valueId];
            }

            $pieces = array_filter(array_merge([$value], [$valueText]));

            $value = implode($this->separator, $pieces);
        }

        $value = preg_replace('/\\s+/siu', ' ', trim(strip_tags($value)));

        return $value;
    }
}
