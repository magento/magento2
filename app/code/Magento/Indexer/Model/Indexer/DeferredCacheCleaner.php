<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model\Indexer;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Indexer\DeferredCacheCleanerInterface;

/**
 * Deferred cache cleaner for indexers
 */
class DeferredCacheCleaner implements DeferredCacheCleanerInterface
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var CacheInterface
     */
    private $appCache;

    /**
     * @var DeferredCacheContext
     */
    private $deferredCacheContext;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * TypeListInterface
     *
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @param EventManager $eventManager
     * @param CacheInterface $appCache
     * @param DeferredCacheContext $deferredCacheContext
     * @param CacheContext $cacheContext
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        EventManager $eventManager,
        CacheInterface $appCache,
        DeferredCacheContext $deferredCacheContext,
        CacheContext $cacheContext,
        TypeListInterface $cacheTypeList
    ) {
        $this->eventManager = $eventManager;
        $this->deferredCacheContext = $deferredCacheContext;
        $this->appCache = $appCache;
        $this->cacheContext = $cacheContext;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Defer cache cleaning until flush() is called
     *
     * @see flush()
     */
    public function start(): void
    {
        $this->deferredCacheContext->start();
    }

    /**
     * Flush cache
     */
    public function flush(): void
    {
        $this->deferredCacheContext->commit();
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        $identities = $this->cacheContext->getIdentities();
        $this->cacheTypeList->cleanType('graphql_query_resolver_result');
        if (!empty($identities)) {
            $this->appCache->clean($identities);
            $this->cacheContext->flush();
        }
    }
}
