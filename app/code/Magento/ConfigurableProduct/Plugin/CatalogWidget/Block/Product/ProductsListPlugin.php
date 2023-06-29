<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\CatalogWidget\Block\Product;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

class ProductsListPlugin
{
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $productCollectionFactory;

    /**
     * @var Visibility
     */
    protected Visibility $catalogProductVisibility;

    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $resource;

    /**
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param ResourceConnection $resource
     */
    public function __construct(
        CollectionFactory  $productCollectionFactory,
        Visibility         $catalogProductVisibility,
        ResourceConnection $resource
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->resource = $resource;
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
        if ($result->count()) {
            $connection = $this->resource->getConnection();
            $productIds = $connection->fetchCol(
                $connection
                ->select()
                ->from(['e' => $this->resource->getTableName('catalog_product_entity')], ['link_table.parent_id'])
                ->joinInner(
                    ['link_table' => $this->resource->getTableName('catalog_product_super_link')],
                    'link_table.product_id = e.entity_id',
                    []
                )
                ->where('link_table.product_id IN (?)', $result->getAllIds())
            );

            $configurableProductCollection = $this->productCollectionFactory->create();
            $configurableProductCollection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
            $configurableProductCollection->addIdFilter($productIds);

            foreach ($configurableProductCollection->getItems() as $item) {
                $result->addItem($item);
            }
        }

        return $result;
    }
}
