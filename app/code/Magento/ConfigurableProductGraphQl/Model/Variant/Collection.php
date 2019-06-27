<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Variant;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection as ChildCollection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;

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
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Product[]
     */
    private $parentProducts = [];

    /**
     * @var array
     */
    private $childrenMap = [];

    /**
     * @var string[]
     */
    private $attributeCodes = [];

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param CollectionFactory $childCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param MetadataPool $metadataPool
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        CollectionFactory $childCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        MetadataPool $metadataPool,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->childCollectionFactory = $childCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->metadataPool = $metadataPool;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Add parent to collection filter
     *
     * @param Product $product
     * @return void
     */
    public function addParentProduct(Product $product) : void
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $productId = $product->getData($linkField);

        if (isset($this->parentProducts[$productId])) {
            return;
        }

        if (!empty($this->childrenMap)) {
            $this->childrenMap = [];
        }
        $this->parentProducts[$productId] = $product;
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
     * @throws \Exception
     */
    private function fetch() : array
    {
        if (empty($this->parentProducts) || !empty($this->childrenMap)) {
            return $this->childrenMap;
        }

        foreach ($this->parentProducts as $product) {
            $attributeData = $this->getAttributesCodes($product);
            /** @var ChildCollection $childCollection */
            $childCollection = $this->childCollectionFactory->create();
            $childCollection->setProductFilter($product);
            $this->collectionProcessor->process(
                $childCollection,
                $this->searchCriteriaBuilder->create(),
                $attributeData
            );

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

    /**
     * Get attributes code
     *
     * @param \Magento\Catalog\Model\Product $currentProduct
     * @return array
     */
    private function getAttributesCodes(Product $currentProduct): array
    {
        $attributeCodes = [];
        $allowAttributes = $currentProduct->getTypeInstance()->getConfigurableAttributes($currentProduct);
        foreach ($allowAttributes as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            if (!\in_array($productAttribute->getAttributeCode(), $attributeCodes)) {
                $attributeCodes[] = $productAttribute->getAttributeCode();
            }
        }

        return $attributeCodes;
    }
}
