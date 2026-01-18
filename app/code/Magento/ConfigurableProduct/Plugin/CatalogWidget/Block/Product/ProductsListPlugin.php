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
     * Configurable products are only added if the child products are not visible individually
     *
     * @param ProductsList $subject
     * @param Collection $result
     * @return Collection
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateCollection(ProductsList $subject, Collection $result): Collection
    {
        $notVisibleCollection = $subject->getBaseCollection();
        $currentIds = $result->getAllIds();
        $searchProducts = array_unique(array_merge($currentIds, $notVisibleCollection->getAllIds()));

        if (empty($searchProducts)) {
            return $result;
        }

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
            ->where('link_table.product_id IN (?)', $searchProducts);
        $visibilityExpr = $connection->getCheckSql(
            'visibility_store.value IS NULL',
            'visibility_default.value',
            'visibility_store.value'
        );
        $select->where(
            $visibilityExpr . ' = ?',
            Visibility::VISIBILITY_NOT_VISIBLE
        );

        $productIds = $connection->fetchCol($select);

        if (empty($productIds)) {
            return $result;
        }

        $visibleCatalogIds = $this->catalogProductVisibility->getVisibleInCatalogIds();
        $configurableProductCollection = $this->productCollectionFactory->create();
        $configurableProductCollection->setVisibility($visibleCatalogIds);
        $configurableProductCollection->addIdFilter($productIds);

        $configurableEntityIds = $configurableProductCollection->getAllIds();
        if (empty($configurableEntityIds)) {
            return $result;
        }

        $filteredCollection = $subject->getBaseCollection();
        $filteredCollection->setVisibility($visibleCatalogIds);
        $filteredCollection->getSelect()->orWhere('e.entity_id IN (?)', $configurableEntityIds);

        return $filteredCollection;
    }
}
