<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductLink\CollectionProvider;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductLink\MapProviderInterface;
use Magento\Catalog\Model\Product\Link;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as LinkedProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory as LinkedProductCollectionFactory;

/**
 * Provides linked products.
 */
class LinkedMapProvider implements MapProviderInterface
{
    /**
     * Link types supported.
     */
    private const TYPES = ['crosssell', 'related', 'upsell'];

    /**
     * Type name => Product model cache key.
     */
    private const PRODUCT_CACHE_KEY_MAP = [
        'crosssell' => 'cross_sell_products',
        'upsell' => 'up_sell_products',
        'related' => 'related_products'
    ];

    /**
     * @var Link
     */
    private $linkModel;

    /**
     * @var MetadataPool
     */
    private $metadata;

    /**
     * @var LinkedProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * LinkedMapProvider constructor.
     * @param Link $linkModel
     * @param MetadataPool $metadataPool
     * @param LinkedProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        Link $linkModel,
        MetadataPool $metadataPool,
        LinkedProductCollectionFactory $productCollectionFactory
    ) {
        $this->linkModel = $linkModel;
        $this->metadata = $metadataPool;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function canProcessLinkType(string $linkType): bool
    {
        return in_array($linkType, self::TYPES, true);
    }

    /**
     * Add linked products to the map.
     *
     * @param Product[][] $map
     * @param string $sku
     * @param string $type
     * @param Product[] $linked
     * @return void
     */
    private function addLinkedToMap(array &$map, string $sku, string $type, array $linked): void
    {
        if (!array_key_exists($sku, $map)) {
            $map[$sku] = [];
        }
        if (!array_key_exists($type, $map[$sku])) {
            $map[$sku][$type] = [];
        }
        $map[$sku][$type] = array_merge($map[$sku][$type], $linked);
    }

    /**
     * Extract cached linked products from entities and find root products that do need a query.
     *
     * @param Product[] $products Products mapped by link field value.
     * @param int[] $types Type requested.
     * @param Product[][] $map Map of linked products.
     * @return string[][] {Type name => Product link field values} map.
     */
    private function processCached(array $products, array $types, array &$map): array
    {
        /** @var string[][] $query */
        $query = [];

        foreach ($products as $productId => $product) {
            $sku = $product->getSku();
            foreach (array_keys($types) as $type) {
                if (array_key_exists($type, self::PRODUCT_CACHE_KEY_MAP)
                    && $product->hasData(self::PRODUCT_CACHE_KEY_MAP[$type])
                ) {
                    $this->addLinkedToMap($map, $sku, $type, $product->getData(self::PRODUCT_CACHE_KEY_MAP[$type]));
                    //Cached found, no need to load.
                    continue;
                }

                if (!array_key_exists($type, $query)) {
                    $query[$type] = [];
                }
                $query[$type][] = $productId;
            }
        }

        return $query;
    }

    /**
     * Load products linked to given products.
     *
     * @param string[][] $productIds {Type name => Product IDs (link field values)} map.
     * @param int[] $types Type name => type ID map.
     * @return Product[][] Type name => Product list map.
     */
    private function queryLinkedProducts(array $productIds, array $types): array
    {
        $found = [];
        foreach ($types as $type => $typeId) {
            if (!array_key_exists($type, $productIds)) {
                continue;
            }

            /** @var LinkedProductCollection $collection */
            $collection = $this->productCollectionFactory->create(['productIds' => $productIds[$type]]);
            $this->linkModel->setLinkTypeId($typeId);
            $collection->setLinkModel($this->linkModel);
            $collection->setIsStrongMode();
            $found[$type] = $collection->getItems();
        }

        return $found;
    }

    /**
     * Cache found linked products for existing root product instances.
     *
     * @param Product[] $forProducts
     * @param Product[][] $map
     * @param int[] $linkTypesRequested Link types that were queried.
     * @return void
     */
    private function cacheLinked(array $forProducts, array $map, array $linkTypesRequested): void
    {
        foreach ($forProducts as $product) {
            $sku = $product->getSku();
            if (!array_key_exists($sku, $map)) {
                $found = [];
            } else {
                $found = $map[$sku];
            }
            foreach (array_keys($linkTypesRequested) as $linkName) {
                if (!array_key_exists($linkName, $found)) {
                    $found[$linkName] = [];
                }
            }

            foreach (self::PRODUCT_CACHE_KEY_MAP as $typeName => $cacheKey) {
                if (!array_key_exists($typeName, $linkTypesRequested)) {
                    //If products were not queried for current type then moving on
                    continue;
                }

                $product->setData($cacheKey, $found[$typeName]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function fetchMap(array $products, array $linkTypes): array
    {
        if (!$products || !$linkTypes) {
            throw new \InvalidArgumentException('Products and link types are required.');
        }

        //Gathering products information
        $productActualIdField = $this->metadata->getMetadata(ProductInterface::class)->getLinkField();
        /** @var Product[] $rootProducts */
        $rootProducts = [];
        /** @var Product $product */
        foreach ($products as $product) {
            if ($id = $product->getData($productActualIdField)) {
                $rootProducts[$id] = $product;
            }
        }
        unset($product);
        //Cannot load without persisted products
        if (!$rootProducts) {
            return [];
        }

        //Finding linked.
        $map = [];
        $query = $this->processCached($rootProducts, $linkTypes, $map);
        $foundLinked = $this->queryLinkedProducts($query, $linkTypes);

        //Filling map with what we've found.
        foreach ($foundLinked as $linkType => $linkedProducts) {
            foreach ($linkedProducts as $linkedProduct) {
                $product = $rootProducts[$linkedProduct->getData('_linked_to_product_id')];
                $this->addLinkedToMap($map, $product->getSku(), $linkType, [$linkedProduct]);
            }
        }

        $this->cacheLinked($rootProducts, $map, $linkTypes);

        return $map;
    }
}
