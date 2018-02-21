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
            throw new NoSuchEntityException(__('Collection provider is not registered'));
        }

        $products = $this->providers[$type]->getLinkedProducts($product);
        $converter = $this->converterPool->getConverter($type);
        $output = [];
        foreach ($products as $item) {
            $output[$item->getId()] = $converter->convert($item);
        }
        return $output;
    }
}
