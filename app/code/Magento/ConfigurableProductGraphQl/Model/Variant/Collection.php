<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Variant;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
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
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * @var int[]
     */
    private $parentIds = [];

    /**
     * @var array
     */
    private $childrenMap = [];

    /**
     * @param CollectionFactory $childCollectionFactory
     * @param ProductFactory $productFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataProvider $productDataProvider
     * @param MetadataPool $metadataPool
     * @param FormatterInterface $formatter
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     */
    public function __construct(
        CollectionFactory $childCollectionFactory,
        ProductFactory $productFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataProvider $productDataProvider,
        MetadataPool $metadataPool,
        FormatterInterface $formatter,
        \Magento\Catalog\Model\ResourceModel\Product $productResource
    ) {
        $this->childCollectionFactory = $childCollectionFactory;
        $this->productFactory = $productFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productDataProvider = $productDataProvider;
        $this->metadataPool = $metadataPool;
        $this->formatter = $formatter;
        $this->productResource = $productResource;
    }

    /**
     * Add parent Id to collection filter
     *
     * @param int $id
     * @return void
     */
    public function addParentId(int $id) : void
    {
        if (!in_array($id, $this->parentIds)) {
            $this->parentIds[] = $id;
        }
    }

    /**
     * Retrieve child products from for passed in parent id.
     *
     * @param int $id
     * @return array|null
     */
    public function getChildProductsByParentId(int $id) : ?array
    {
        $childrenMap = $this->fetch();

        if (!isset($childrenMap[$id])) {
            return null;
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
        /** @var ChildCollection $childCollection */
        $childCollection = $this->childCollectionFactory->create();
        foreach ($this->parentIds as $id) {
            /** @var Product $product */
            $product = $this->productFactory->create();
            $product->setData($linkField, $id);
            $childCollection->setProductFilter($product);
        }

        $childIds = [];
        foreach ($childCollection->getItems() as $childProduct) {
            $childIds[] = (int)$childProduct->getData($linkField);
        }

        $this->searchCriteriaBuilder->addFilter($linkField, $childIds, 'in');
        $childProducts = $this->productDataProvider->getList($this->searchCriteriaBuilder->create());

        /** @var Product $childProduct */
        foreach ($childProducts->getItems() as $childProduct) {
            $formattedChild = $this->formatter->format($childProduct);
            $categoryLinks = $this->productResource->getCategoryIds($childProduct);
            /** @var Product $item */
            foreach ($childCollection->getItems() as $item) {
                if ($childProduct->getId() !== $item->getId()) {
                    continue;
                }

                $parentId = $item->getParentId();
            }
            foreach ($categoryLinks as $position => $link) {
                $formattedChild['category_links'][] = ['position' => $position, 'category_id' => $link];
            }
            if (!isset($this->childrenMap[$parentId])) {
                $this->childrenMap[$parentId] = [];
            }

            $this->childrenMap[$parentId][] = $formattedChild;
        }

        return $this->childrenMap;
    }
}
