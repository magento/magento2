<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Model\ProductLink\Converter\ConverterPool;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class \Magento\Catalog\Model\ProductLink\CollectionProvider
 *
 * @since 2.0.0
 */
class CollectionProvider
{
    /**
     * @var CollectionProviderInterface[]
     * @since 2.0.0
     */
    protected $providers;

    /**
     * @var ConverterPool
     * @since 2.0.0
     */
    protected $converterPool;

    /**
     * @param ConverterPool $converterPool
     * @param CollectionProviderInterface[] $providers
     * @since 2.0.0
     */
    public function __construct(ConverterPool $converterPool, array $providers = [])
    {
        $this->converterPool = $converterPool;
        $this->providers = $providers;
    }

    /**
     * Get product collection by link type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $type
     * @return array
     * @throws NoSuchEntityException
     * @since 2.0.0
     */
    public function getCollection(\Magento\Catalog\Model\Product $product, $type)
    {
        if (!isset($this->providers[$type])) {
            throw new NoSuchEntityException(__('Collection provider is not registered'));
        }

        $products = $this->providers[$type]->getLinkedProducts($product);
        $converter = $this->converterPool->getConverter($type);
        $output = [];
        $sorterItems = [];
        foreach ($products as $item) {
            $output[$item->getId()] = $converter->convert($item);
        }

        foreach ($output as $item) {
            $itemPosition = $item['position'];
            if (!isset($sorterItems[$itemPosition])) {
                $sorterItems[$itemPosition] = $item;
            } else {
                $newPosition = $itemPosition + 1;
                $sorterItems[$newPosition] = $item;
            }
        }
        ksort($sorterItems);
        return $sorterItems;
    }
}
