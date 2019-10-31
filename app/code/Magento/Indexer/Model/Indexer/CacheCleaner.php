<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model\Indexer;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Indexer\CacheContext;

/**
 * Clean cache for reindexed entities after executed action.
 */
class CacheCleaner
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var CacheInterface
     */
    private $appCache;

    /**
     * @param EventManager $eventManager
     * @param CacheContext $cacheContext
     * @param CacheInterface $appCache
     */
    public function __construct(
        EventManager $eventManager,
        CacheContext $cacheContext,
        CacheInterface $appCache
    ) {
        $this->eventManager = $eventManager;
        $this->cacheContext = $cacheContext;
        $this->appCache = $appCache;
    }

    /**
     * Clean cache after full reindex.
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteFull(ActionInterface $subject)
    {
        $this->cleanCache();
    }

    /**
     * Clean cache after reindexed list.
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteList(ActionInterface $subject)
    {
        $this->cleanCache();
    }

    /**
     * Clean cache after reindexed row.
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteRow(ActionInterface $subject)
    {
        $this->cleanCache();
    }

    /**
     * Clean cache.
     *
     * @return void
     */
    private function cleanCache()
    {
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);

        $identities = $this->cacheContext->getIdentities();
        if (!empty($identities)) {
            $this->appCache->clean($identities);
        }
    }
}
