<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Plugin;

use Magento\Framework\App\CacheInterface;

/**
 *  Clean configurable product and its children cache after saving product model
 */
class CleanCache
{
    /**
     * Application Cache Manager
     *
     * @var CacheInterface
     */
    private $cacheManager;

    /**
     * @param CacheInterface $cacheManager
     */
    public function __construct(CacheInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param \Magento\Catalog\Model\Product $subject
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCleanCache(
        \Magento\Catalog\Model\Product $subject,
        $product
    ) {
        $items = $product->getCollection()->getItems();
        foreach ($items as $item) {
            if ($item->getId()) {
                $this->cacheManager->clean(
                    \Magento\Catalog\Model\Product::CACHE_TAG . '_' . $item->getId()
                );
            }
        }
    }
}
