<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductLink\Converter\ConverterPool;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Provides a collection of linked product items (crosssells, related, upsells, ...)
 */
class CollectionProvider
{
    /**
     * @var CollectionProviderInterface[]
     */
    protected $providers;

    /**
     * @var MapProviderInterface[]
     */
    private $mapProviders;

    /**
     * @var ConverterPool
     */
    protected $converterPool;

    /**
     * @param ConverterPool $converterPool
     * @param CollectionProviderInterface[] $providers
     * @param MapProviderInterface[] $mapProviders
     */
    public function __construct(ConverterPool $converterPool, array $providers = [], array $mapProviders = [])
    {
        $this->converterPool = $converterPool;
        $this->providers = $providers;
        $this->mapProviders = $mapProviders;
    }

    /**
     * Extract link data from linked products.
     *
     * @param Product[] $linkedProducts
     * @param string $type
     * @return array
     */
    private function prepareList(array $linkedProducts, string $type): array
    {
        $converter = $this->converterPool->getConverter($type);
        $links = [];
        foreach ($linkedProducts as $item) {
            $itemId = $item->getId();
            $links[$itemId] = $converter->convert($item);
            $links[$itemId]['position'] = $links[$itemId]['position'] ?? 0;
            $links[$itemId]['link_type'] = $type;
        }

        return $links;
    }

    /**
     * Get product collection by link type
     *
     * @param Product $product
     * @param string $type
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCollection(Product $product, $type)
    {
        if (!isset($this->providers[$type])) {
            throw new NoSuchEntityException(__("The collection provider isn't registered."));
        }

        $products = $this->providers[$type]->getLinkedProducts($product);

        $linkData = $this->prepareList($products, $type);
        usort(
            $linkData,
            function (array $itemA, array $itemB): int {
                $posA = (int)$itemA['position'];
                $posB = (int)$itemB['position'];

                return $posA <=> $posB;
            }
        );

        return $linkData;
    }

    /**
     * Load maps from map providers.
     *
     * @param array $map
     * @param array $typeProcessors
     * @param Product[] $products
     * @return void
     */
    private function retrieveMaps(array &$map, array $typeProcessors, array $products): void
    {
        /**
         * @var MapProviderInterface $processor
         * @var string[] $types
         */
        foreach ($typeProcessors as $processorIndex => $types) {
            $typeMap = $this->mapProviders[$processorIndex]->fetchMap($products, $types);
            /**
             * @var string $sku
             * @var Product[][] $links
             */
            foreach ($typeMap as $sku => $links) {
                $linkData = [];
                foreach ($links as $linkType => $linkedProducts) {
                    $linkData[] = $this->prepareList($linkedProducts, $linkType);
                }
                if ($linkData) {
                    $existing = [];
                    if (array_key_exists($sku, $map)) {
                        $existing = $map[$sku];
                    }
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $map[$sku] = array_merge($existing, ...$linkData);
                }
            }
        }
    }

    /**
     * Load links for each product separately.
     *
     * @param \SplObjectStorage $map
     * @param string[] $types
     * @param Product[] $products
     * @return void
     * @throws NoSuchEntityException
     */
    private function retrieveSingles(array &$map, array $types, array $products): void
    {
        foreach ($products as $product) {
            $linkData = [];
            foreach ($types as $type) {
                $linkData[] = $this->getCollection($product, $type);
            }
            $linkData = array_filter($linkData);
            if ($linkData) {
                $existing = [];
                if (array_key_exists($product->getSku(), $map)) {
                    $existing = $map[$product->getSku()];
                }
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $map[$product->getSku()] = array_merge($existing, ...$linkData);
            }
        }
    }

    /**
     * Load map of linked product data.
     *
     * Link data consists of link_type, type, sku, position, extension attributes? and custom_attributes?.
     *
     * @param Product[] $products
     * @param array $types Keys - string names, values - codes.
     * @return array Keys - SKUs, values containing link data.
     * @throws NoSuchEntityException
     * @throws \InvalidArgumentException
     */
    public function getMap(array $products, array $types): array
    {
        if (!$types) {
            throw new \InvalidArgumentException('Types are required');
        }
        $map = [];
        $typeProcessors = [];
        /** @var string[] $singleProcessors */
        $singleProcessors = [];
        //Finding map processors
        foreach ($types as $type => $typeCode) {
            foreach ($this->mapProviders as $i => $mapProvider) {
                if ($mapProvider->canProcessLinkType($type)) {
                    if (!array_key_exists($i, $typeProcessors)) {
                        $typeProcessors[$i] = [];
                    }
                    $typeProcessors[$i][$type] = $typeCode;
                    continue 2;
                }
            }
            //No map processor found, will process 1 by 1
            $singleProcessors[] = $type;
        }

        $this->retrieveMaps($map, $typeProcessors, $products);
        $this->retrieveSingles($map, $singleProcessors, $products);

        return $map;
    }
}
