<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Variant;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection as ChildCollection;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as DataProvider;

/**
 * Collection for fetching configurable child product data.
 */
class Collection
{
    /**
     * @var CollectionFactory
     */
    private $childCollectionFactory;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DataProvider
     */
    private $productDataProvider;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var int[]
     */
    private $parentIds = [];

    /**
     * @var array
     */
    private $childrenMap = [];

    /**
     * @var string[]
     */
    private $attributeCodes = [];

    /**
     * @param CollectionFactory $childCollectionFactory
     * @param ProductFactory $productFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataProvider $productDataProvider
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        CollectionFactory $childCollectionFactory,
        ProductFactory $productFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataProvider $productDataProvider,
        MetadataPool $metadataPool
    ) {
        $this->childCollectionFactory = $childCollectionFactory;
        $this->productFactory = $productFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productDataProvider = $productDataProvider;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Add parent Id to collection filter
     *
     * @param int $id
     * @return void
     */
    public function addParentId(int $id) : void
    {
        if (!in_array($id, $this->parentIds) && !empty($this->childrenMap)) {
            $this->childrenMap = [];
            $this->parentIds[] = $id;
        } elseif (!in_array($id, $this->parentIds)) {
            $this->parentIds[] = $id;
        }
    }

    /**
     * Add attributes to collection filter
     *
     * @param array $attributeCodes
     * @return void
     */
    public function addEavAttributes(array $attributeCodes) : void
    {
        $this->attributeCodes = array_replace($this->attributeCodes, $attributeCodes);
    }

    /**
     * Retrieve child products from for passed in parent id.
     *
     * @param int $id
     * @return array
     */
    public function getChildProductsByParentId(int $id) : array
    {
        $childrenMap = $this->fetch();

        if (!isset($childrenMap[$id])) {
            return [];
        }

        return $childrenMap[$id];
    }

    /**
     * Fetch all children products from parent id's.
     *
     * @return array
     */
    private function fetch() : array
    {
        if (empty($this->parentIds) || !empty($this->childrenMap)) {
            return $this->childrenMap;
        }

        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        foreach ($this->parentIds as $id) {
            /** @var ChildCollection $childCollection */
            $childCollection = $this->childCollectionFactory->create();
            /** @var Product $product */
            $product = $this->productFactory->create();
            $product->setData($linkField, $id);
            $childCollection->setProductFilter($product);

            /** @var Product $childProduct */
            foreach ($childCollection->getItems() as $childProduct) {
                $formattedChild = ['model' => $childProduct, 'sku' => $childProduct->getSku()];
                $parentId = (int)$childProduct->getParentId();
                if (!isset($this->childrenMap[$parentId])) {
                    $this->childrenMap[$parentId] = [];
                }

                $this->childrenMap[$parentId][] = $formattedChild;
            }
        }

        return $this->childrenMap;
    }
}
