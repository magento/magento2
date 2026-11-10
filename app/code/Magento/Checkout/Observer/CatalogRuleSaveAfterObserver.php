<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Observer;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\ObserverInterface;

/**
 * Sets a cache flag when a catalog price rule is saved so the cart page can refresh totals only when needed.
 */
class CatalogRuleSaveAfterObserver implements ObserverInterface
{
    /**
     * Cache key for catalog price rules updated timestamp (used by AbstractCart to decide whether to recollect).
     */
    public const CACHE_KEY_CATALOG_RULES_UPDATED_AT = 'checkout_cart_catalog_rules_updated_at';

    /**
     * Cache lifetime (7 days).
     */
    private const CACHE_LIFETIME = 604800;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * When a catalog rule is saved, set cache timestamp so storefront cart can refresh totals once.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $this->cache->save(
            (string) time(),
            self::CACHE_KEY_CATALOG_RULES_UPDATED_AT,
            [],
            self::CACHE_LIFETIME
        );
    }
}
