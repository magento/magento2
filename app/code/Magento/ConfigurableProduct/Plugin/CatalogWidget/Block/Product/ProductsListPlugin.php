<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\CatalogWidget\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;

class ProductsListPlugin
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var MetadataPool
     */
    private MetadataPool $metadataPool;

    /**
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resource,
        MetadataPool $metadataPool
    ) {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Adds configurable products to the item list if child products are already part of the collection
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
        $searchProducts = array_merge($currentIds, $notVisibleCollection->getAllIds());

        if (!empty($searchProducts)) {
            $linkField = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
            $connection = $this->resource->getConnection();
            $productIds = $connection->fetchCol(
                $connection
                    ->select()
                    ->from(
                        ['e' => $this->resource->getTableName('catalog_product_entity')],
                        ['entity_table.entity_id']
                    )
                    ->joinInner(
                        ['link_table' => $this->resource->getTableName('catalog_product_super_link')],
                        'link_table.product_id = e.entity_id',
                        []
                    )
                    ->joinInner(
                        ['entity_table' => $this->resource->getTableName('catalog_product_entity')],
                        'entity_table.' . $linkField . ' = link_table.parent_id',
                        []
                    )
                    ->where('link_table.product_id IN (?)', $searchProducts)
            );

            if (empty($productIds)) {
                return $result;
            }
            $this->addParentProductsToSelect($result, $productIds);
        }

        return $result;
    }

    /**
     * @param Collection $result The product collection
     * @param array $productIds Array of parent product IDs to include
     * @return void
     */
    protected function addParentProductsToSelect(Collection $result, array $productIds): void
    {
        $connection = $this->resource->getConnection();
        $select = $result->getSelect();
        $originalWhere = $select->getPart(Select::WHERE);
        if (!empty($originalWhere)) {
            $originalWhere = array_filter($originalWhere, 'strlen');
        }
        $parentCondition = $connection->quoteInto('e.entity_id IN (?)', $productIds);

        $newWhere = [];
        if (!empty($originalWhere)) {
            $newWhere[] = '(' . implode(' ', $originalWhere) . ')';
        }
        $newWhere[] = $parentCondition;
        $select->reset(Select::WHERE);
        $select->where(new \Zend_Db_Expr(implode(' OR ', $newWhere)));
    }
}
