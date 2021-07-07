<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
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
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @param ResourceConnection $resource
     * @param Type $catalogProductType
     * @param EngineProvider $engineProvider
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param AttributeRepositoryInterface $attributeRepository
     * @throws \Exception
     */
    public function __construct(
        ResourceConnection $resource,
        Type $catalogProductType,
        EngineProvider $engineProvider,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->storeManager = $storeManager;
        $this->engine = $engineProvider->get();
        $this->metadata = $metadataPool->getMetadata(ProductInterface::class);
        $this->attributeRepository = $attributeRepository;
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
     * @throws NoSuchEntityException
     */
    private function joinAttribute(Select $select, string $attributeCode, int $storeId, array $whereValue)
    {
        $linkField = $this->metadata->getLinkField();
        $attribute = $this->attributeRepository->get(Product::ENTITY, $attributeCode);
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
}
