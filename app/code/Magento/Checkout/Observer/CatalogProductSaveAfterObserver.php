<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Observer;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Block\ProductPricesUpdated;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\EntityManager\EventManager;
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
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var ProductPricesUpdated
     */
    private $productPricesUpdated;

    /**
     * @param CacheInterface $cache
     * @param Config $cacheConfig
     * @param EventManager $eventManager
     * @param ProductPricesUpdated $productPricesUpdated
     */
    public function __construct(
        CacheInterface $cache,
        Config $cacheConfig,
        EventManager $eventManager,
        ProductPricesUpdated $productPricesUpdated
    ) {
        $this->cache = $cache;
        $this->cacheConfig = $cacheConfig;
        $this->eventManager = $eventManager;
        $this->productPricesUpdated = $productPricesUpdated;
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
            $this->cache->clean($this->productPricesUpdated->getIdentities());
            $this->eventManager->dispatch(
                'catalog_product_price_changed',
                [
                    'object' => $this->productPricesUpdated,
                ]
            );
        }
    }
}
