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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Collection for fetching options for all configurable options pulled back in result set.
 */
class Collection
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'configurable';

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

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param CollectionFactory $attributeCollectionFactory
     * @param ProductFactory $productFactory
     * @param MetadataPool $metadataPool
     * @param Uid|null $uidEncoder
     */
    public function __construct(
        CollectionFactory $attributeCollectionFactory,
        ProductFactory $productFactory,
        MetadataPool $metadataPool,
        Uid $uidEncoder = null
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->productFactory = $productFactory;
        $this->metadataPool = $metadataPool;
        $this->uidEncoder = $uidEncoder ?: ObjectManager::getInstance()
            ->get(Uid::class);
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
     * Retrieve attributes for given product id or empty array
     *
     * @param int $productId
     * @return array
     */
    public function getAttributesByProductId(int $productId): array
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
        if (empty($this->productIds) || array_key_exists(end($this->productIds), $this->attributeMap)) {
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
            $this->attributeMap[$productId][$attribute->getId()]['uid'] = $this->uidEncoder->encode(
                self::OPTION_TYPE . '/' . $productId . '/' . $attribute->getAttributeId()
            );
            $this->attributeMap[$productId][$attribute->getId()]['attribute_id_v2'] =
                $attribute->getProductAttribute()->getAttributeId();
            $this->attributeMap[$productId][$attribute->getId()]['attribute_uid'] =
                $this->uidEncoder->encode((string) $attribute->getProductAttribute()->getAttributeId());
            $this->attributeMap[$productId][$attribute->getId()]['product_uid'] =
                $this->uidEncoder->encode((string) $attribute->getProductId());
            $this->attributeMap[$productId][$attribute->getId()]['attribute_code'] =
                $attribute->getProductAttribute()->getAttributeCode();
            $this->attributeMap[$productId][$attribute->getId()]['values'] = array_map(
                function ($value) use ($attribute) {
                    $value['attribute_id'] = $attribute->getAttributeId();
                    return $value;
                },
                $attributeData['options']
            );
            $this->attributeMap[$productId][$attribute->getId()]['label']
                = $attribute->getProductAttribute()->getStoreLabel();
        }

        return $this->attributeMap;
    }
}
