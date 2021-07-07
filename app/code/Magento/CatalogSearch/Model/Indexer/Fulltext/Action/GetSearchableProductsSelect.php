<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db;

class GetSearchableProductsSelect
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var EntityMetadata
     */
    private $metadata;
    /**
     * @var Config
     */
    private $eavConfig;
    /**
     * @var CollectionFactory
     */
    private $productAttributeCollectionFactory;
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    private $searchableAttributes;
    private $searchableAttributesByBackendType;

    /**
     * @param ResourceConnection $resource
     * @param Type $catalogProductType
     * @param Config $eavConfig
     * @param CollectionFactory $prodAttributeCollectionFactory
     * @param EngineProvider $engineProvider
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @throws \Exception
     */
    public function __construct(
        ResourceConnection $resource,
        Type $catalogProductType,
        Config $eavConfig,
        CollectionFactory $prodAttributeCollectionFactory,
        EngineProvider $engineProvider,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->eavConfig = $eavConfig;
        $this->productAttributeCollectionFactory = $prodAttributeCollectionFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->engine = $engineProvider->get();
        $this->metadata = $metadataPool->getMetadata(ProductInterface::class);
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
     * @throws NoSuchEntityException
     */
    public function execute(
        int $storeId,
        array $staticFields,
        $productIds,
        int $lastProductId,
        int $batch
    ): Select
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $select = $this->connection->select()
            ->useStraightJoin(true)
            ->from(
                ['e' => $this->resource->getTableName('catalog_product_entity')],
                array_merge(['entity_id', 'type_id'], $staticFields)
            )
            ->join(
                ['website' => $this->resource->getTableName('catalog_product_website')],
                $this->connection->quoteInto('website.product_id = e.entity_id AND website.website_id = ?', $websiteId),
                []
            );

        $this->joinAttribute($select, 'visibility', $storeId, $this->engine->getAllowedVisibility());
        $this->joinAttribute($select, 'status', $storeId, [Status::STATUS_ENABLED]);

        if ($productIds !== null) {
            $select->where('e.entity_id IN (?)', $productIds, Zend_Db::INT_TYPE);
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
        $attributeTable = $this->resource->getTableName('catalog_product_entity_' . $attribute->getBackendType());
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
     * @return Attribute[]
     * @throws LocalizedException
     * @since 100.0.3
     */
    private function getSearchableAttributes($backendType = null)
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
     * @throws LocalizedException
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
     * @return Attribute
     * @throws LocalizedException
     * @since 100.0.3
     */
    private function getSearchableAttribute($attribute)
    {
        $attributes = $this->getSearchableAttributes();
        if (isset($attributes[$attribute])) {
            return $attributes[$attribute];
        }

        return $this->eavConfig->getAttribute(Product::ENTITY, $attribute);
    }
}
