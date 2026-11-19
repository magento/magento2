<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\CatalogWidget\Block\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class ProductsListPlugin
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $productCollectionFactory;

    /**
     * @var Visibility
     */
    private Visibility $catalogProductVisibility;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var MetadataPool
     */
    private MetadataPool $metadataPool;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(
        CollectionFactory  $productCollectionFactory,
        Visibility         $catalogProductVisibility,
        ResourceConnection $resource,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * Adds configurable products to the item list if child products are already part of the collection
     *
     * Configurable products are only added if the child products are not visible individually. The products
     * matching the widget condition are handed to the database as a subquery: a condition can match a large
     * share of the catalog, and reading those ids into PHP to send them back in an IN list is what made the
     * widget slow.
     *
     * @param ProductsList $subject
     * @param Collection $result
     * @return Collection
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateCollection(ProductsList $subject, Collection $result): Collection
    {
        // The base collection differs from the result only by the visibility filter, so it holds every matching id.
        $matchingProductIds = $subject->getBaseCollection()->getAllIdsSql()->resetJoinLeft();

        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)
            ->getLinkField();
        $connection = $this->resource->getConnection();
        $visibilityAttributeId = $this->config->getAttribute(Product::ENTITY, 'visibility')->getId();
        $storeId = $this->storeManager->getStore()->getId();

        $select = $connection->select()
            ->from(
                ['e' => $this->resource->getTableName('catalog_product_entity')],
                []
            )
            ->joinInner(
                ['link_table' => $this->resource->getTableName('catalog_product_super_link')],
                'link_table.product_id = e.entity_id',
                []
            )
            ->joinInner(
                ['entity_table' => $this->resource->getTableName('catalog_product_entity')],
                'entity_table.' . $linkField . ' = link_table.parent_id',
                ['entity_table.entity_id']
            )
            ->joinInner(
                ['visibility_default' => $this->resource->getTableName('catalog_product_entity_int')],
                implode(' AND ', [
                    'visibility_default.' . $linkField . ' = e.' . $linkField,
                    $connection->quoteInto('visibility_default.attribute_id = ?', $visibilityAttributeId),
                    'visibility_default.store_id = 0',
                ]),
                []
            )
            ->joinLeft(
                ['visibility_store' => $this->resource->getTableName('catalog_product_entity_int')],
                implode(' AND ', [
                    'visibility_store.' . $linkField . ' = e.' . $linkField,
                    $connection->quoteInto('visibility_store.attribute_id = ?', $visibilityAttributeId),
                    $connection->quoteInto('visibility_store.store_id = ?', $storeId),
                ]),
                []
            )
            ->where('link_table.product_id IN (?)', $matchingProductIds);
        $visibilityExpr = $connection->getCheckSql(
            'visibility_store.value IS NULL',
            'visibility_default.value',
            'visibility_store.value'
        );
        $select->where(
            $visibilityExpr . ' = ?',
            Visibility::VISIBILITY_NOT_VISIBLE
        );

        $visibleCatalogIds = $this->catalogProductVisibility->getVisibleInCatalogIds();
        $configurableProductCollection = $this->productCollectionFactory->create();
        $configurableProductCollection->setVisibility($visibleCatalogIds);
        $configurableProductCollection->addFieldToFilter('entity_id', ['in' => $select]);

        // Only the configurable ids are read back: an IN subquery behind OR is re-evaluated for every row
        // the widget query examines, whereas the id list is matched once.
        $configurableEntityIds = $configurableProductCollection->getAllIds();
        if (empty($configurableEntityIds)) {
            return $result;
        }

        $result->getSelect()->orWhere('e.entity_id IN (?)', $configurableEntityIds);

        return $result;
    }
}
