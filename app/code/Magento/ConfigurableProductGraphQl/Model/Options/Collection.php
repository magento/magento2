<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Options;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection
    as AttributeCollection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\CollectionFactory;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Collection for fetching options for all configurable options pulled back in result set.
 */
class Collection
{
    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var int[]
     */
    private $productIds = [];

    /**
     * @var array
     */
    private $attributeMap = [];

    /**
     * @param CollectionFactory $attributeCollectionFactory
     * @param ProductFactory $productFactory
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        CollectionFactory $attributeCollectionFactory,
        ProductFactory $productFactory,
        MetadataPool $metadataPool
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->productFactory = $productFactory;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Add product id to attribute collection filter.
     *
     * @param int $productId
     */
    public function addProductId(int $productId) : void
    {
        if (!in_array($productId, $this->productIds)) {
            $this->productIds[] = $productId;
        }
    }

    /**
     * Retrieve attributes for given product id or empty array
     *
     * @param int $productId
     * @return array
     */
    public function getAttributesByProductId(int $productId) : array
    {
        $attributes = $this->fetch();

        if (!isset($attributes[$productId])) {
            return [];
        }

        return $attributes[$productId];
    }

    /**
     * Fetch attribute data
     *
     * @return array
     */
    private function fetch() : array
    {
        if (empty($this->productIds) || !empty($this->attributeMap)) {
            return $this->attributeMap;
        }

        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        /** @var AttributeCollection $attributeCollection */
        $attributeCollection = $this->attributeCollectionFactory->create();
        foreach ($this->productIds as $id) {
            /** @var Product $product */
            $product = $this->productFactory->create();
            $product->setData($linkField, $id);
            $attributeCollection->setProductFilter($product);
        }

        /** @var Attribute $attribute */
        foreach ($attributeCollection->getItems() as $attribute) {
            $productId = (int)$attribute->getProductId();
            if (!isset($this->attributeMap[$productId])) {
                $this->attributeMap[$productId] = [];
            }

            $attributeData = $attribute->getData();
            $this->attributeMap[$productId][$attribute->getId()] = $attribute->getData();
            $this->attributeMap[$productId][$attribute->getId()]['id'] = $attribute->getId();
            $this->attributeMap[$productId][$attribute->getId()]['attribute_id_v2']
                = $attribute->getProductAttribute()->getAttributeId();
            $this->attributeMap[$productId][$attribute->getId()]['attribute_code']
                = $attribute->getProductAttribute()->getAttributeCode();
            $this->attributeMap[$productId][$attribute->getId()]['values'] = $attributeData['options'];
            $this->attributeMap[$productId][$attribute->getId()]['label']
                = $attribute->getProductAttribute()->getStoreLabel();
        }

        return $this->attributeMap;
    }
}
