<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model\Indexer;

use Magento\Framework\Indexer\CacheContext;

/**
 * Deferred cache context for indexers
 */
class DeferredCacheContext
{
    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var array
     */
    private $tags = [];

    /**
     * @var array
     */
    private $entities = [];

    /**
     * @var int
     */
    private $level = 0;

    /**
     * @param CacheContext $cacheContext
     */
    public function __construct(CacheContext $cacheContext)
    {
        $this->cacheContext = $cacheContext;
    }

    /**
     * Register entity Ids
     *
     * @param string $cacheTag
     * @param array $ids
     */
    public function registerEntities(string $cacheTag, array $ids): void
    {
        if ($this->isActive()) {
            $this->entities[$cacheTag] = array_merge($this->entities[$cacheTag] ?? [], $ids);
        }
    }

    /**
     * Register entity tags
     *
     * @param array $cacheTags
     */
    public function registerTags(array $cacheTags): void
    {
        if ($this->isActive()) {
            $this->tags = array_merge($this->tags, $cacheTags);
        }
    }

    /**
     * Defer any subsequent cache tags registration until commit() is called
     *
     * @see commit()
     */
    public function start(): void
    {
        if (!$this->isActive()) {
            $this->entities = [];
            $this->tags = [];
            $this->level = 0;
        }
        ++$this->level;
    }

    /**
     * Register all buffered cache tags since the first call of start()
     *
     * @see start()
     */
    public function commit(): void
    {
        $level = $this->level--;
        if ($level === 1) {
            if ($this->tags) {
                $this->cacheContext->registerTags($this->tags);
            }
            foreach ($this->entities as $cacheTag => $entityIds) {
                $this->cacheContext->registerEntities($cacheTag, $entityIds);
            }
        }
    }

    /**
     * Check if cache tags registration is deferred
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->level > 0;
    }
}
