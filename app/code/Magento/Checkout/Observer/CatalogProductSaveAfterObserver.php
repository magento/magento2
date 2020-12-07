<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Observer;

use Magento\CacheInvalidate\Model\PurgeCache;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Block\ProductPricesUpdated;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\PageCache\Model\Config;

/**
 * Observer on catalog_product_save_after event.
 */
class CatalogProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var Config
     */
    private $cacheConfig;

    /**
     * @var PurgeCache
     */
    private $purgeCache;

    /**
     * @param CacheInterface $cache
     * @param Config $cacheConfig
     * @param PurgeCache $purgeCache
     */
    public function __construct(
        CacheInterface $cache,
        Config $cacheConfig,
        PurgeCache $purgeCache
    ) {
        $this->cache = $cache;
        $this->cacheConfig = $cacheConfig;
        $this->purgeCache = $purgeCache;
    }

    /**
     * Invalidate ProductPricesUpdated block cache on product price changes.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();
        if ($product->dataHasChangedFor('price')) {
            $this->cache->clean(ProductPricesUpdated::CACHE_TAG);
            if ($this->cacheConfig->isEnabled() && $this->cacheConfig->getType() == Config::VARNISH) {
                $this->purgeCache->sendPurgeRequest([ProductPricesUpdated::CACHE_TAG]);
            }
        }
    }
}
