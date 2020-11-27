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
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

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
     * @param CacheInterface $cache
     */
    public function __construct(
        CacheInterface $cache
    ) {
        $this->cache = $cache;
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
        }
    }
}
