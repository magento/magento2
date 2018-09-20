<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Model\ProductLink\Converter\ConverterPool;
use Magento\Framework\Exception\NoSuchEntityException;

class CollectionProvider
{
    /**
     * @var CollectionProviderInterface[]
     */
    protected $providers;

    /**
     * @var ConverterPool
     */
    protected $converterPool;

    /**
     * @param ConverterPool $converterPool
     * @param CollectionProviderInterface[] $providers
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
     */
    public function getCollection(\Magento\Catalog\Model\Product $product, $type)
    {
        if (!isset($this->providers[$type])) {
            throw new NoSuchEntityException(__("The collection provider isn't registered."));
        }

        $products = $this->providers[$type]->getLinkedProducts($product);
        $converter = $this->converterPool->getConverter($type);
        $sorterItems = [];
        foreach ($products as $item) {
            $sorterItems[$item->getId()] = $converter->convert($item);
        }

        usort($sorterItems, [$this, 'comparePosition']);

        return $sorterItems;
    }

    /**
     * Compare item by key 'position'
     *
     * @param array $a
     * @param array $b
     * @return int It returns -1, 0 or 1 when $a['position'] is respectively less than,
     * equal to, or greater than $a['position']
     */
    public function comparePosition(array $a, array $b): int
    {
        return (int)$a['position'] <=> (int)$b['position'];
    }
}
