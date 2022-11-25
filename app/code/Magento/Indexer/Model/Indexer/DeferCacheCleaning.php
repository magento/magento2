<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model\Indexer;

use Magento\Framework\Indexer\CacheContext;

/**
 * Defer cache tags registration if cache context is deferred
 */
class DeferCacheCleaning
{
    /**
     * @var DeferredCacheContext
     */
    private $deferredCacheContext;

    /**
     * @param DeferredCacheContext $deferredCacheContext
     */
    public function __construct(
        DeferredCacheContext $deferredCacheContext
    ) {
        $this->deferredCacheContext = $deferredCacheContext;
    }

    /**
     * Defer cache tags registration if cache context is deferred
     *
     * @param CacheContext $subject
     * @param callable $proceed
     * @param string $cacheTag
     * @param array $ids
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRegisterEntities(
        CacheContext $subject,
        callable $proceed,
        string $cacheTag,
        array $ids
    ): CacheContext {
        if ($this->deferredCacheContext->isActive()) {
            $this->deferredCacheContext->registerEntities($cacheTag, $ids);
        } else {
            $proceed($cacheTag, $ids);
        }
        return $subject;
    }

    /**
     * Defer cache tags registration if cache context is deferred
     *
     * @param CacheContext $subject
     * @param callable $proceed
     * @param array $cacheTags
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRegisterTags(
        CacheContext $subject,
        callable $proceed,
        array $cacheTags
    ): CacheContext {
        if ($this->deferredCacheContext->isActive()) {
            $this->deferredCacheContext->registerTags($cacheTags);
        } else {
            $proceed($cacheTags);
        }
        return $subject;
    }
}
