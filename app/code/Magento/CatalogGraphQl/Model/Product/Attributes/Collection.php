<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Product\Attributes;

use Magento\CatalogGraphQl\Model\Resolver\Products\Attributes\Collection as AttributesCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend;
use Magento\Framework\Phrase;

/**
 * Collection for fetching custom attributes for products in filter.
 */
class Collection
{
    /**
     * @var int[]
     */
    private $productIds = [];

    /**
     * @var array
     */
    private $attributeValueMap = [];

    /**
     * @var AttributesCollection
     */
    private $attributesCollection;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DefaultFrontend
     */
    private $frontend;

    /**
     * @param AttributesCollection $attributesCollection
     * @param CollectionFactory collectionFactory
     * @param DefaultFrontend $frontend
     */
    public function __construct(
        AttributesCollection $attributesCollection,
        CollectionFactory $collectionFactory,
        DefaultFrontend $frontend
    ) {
        $this->attributesCollection = $attributesCollection;
        $this->collectionFactory = $collectionFactory;
        $this->frontend = $frontend;
    }

    /**
     * Add product id to attribute collection filter.
     *
     * @param int $productId
     */
    public function addProductId(int $productId): void
    {
        if (!in_array($productId, $this->productIds)) {
            $this->productIds[] = $productId;
        }
    }

    /**
     * Retrieve attributes values for given product id or empty array
     *
     * @param int $productId
     * @return array
     */
    public function getAttributesValueByProductId(int $productId): array
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
    private function fetch(): array
    {
        if (empty($this->productIds) || !empty($this->attributeValueMap)) {
            return $this->attributeValueMap;
        }
        $attributes = $this->attributesCollection->getAttributes();
        $products = $this->collectionFactory->create()->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', array('in' => $this->productIds));
        foreach ($products as $productId => $product) {
            $data = [];
            foreach ($attributes as $attribute) {
                $value = $this->frontend->setAttribute($attribute)->getValue($product);
                if ($value instanceof Phrase) {
                    $value = (string) $value;
                }
                $value = $value ? $value : null;
                $data[] = [
                    'value' => $value,
                    'code' => $attribute->getAttributeCode(),
                ];
            }
            $this->attributeValueMap[$productId] = $data;
        }
        return $this->attributeValueMap;
    }
}
