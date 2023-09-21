<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * CacheableQuery should be used as a singleton for collecting HTTP cache-related info and tags of all entities.
 */
class CacheableQuery implements ResetAfterRequestInterface
{
    /**
     * @var string[]
     */
    private $cacheTags = [];

    /**
     * @var bool
     */
    private $cacheable = true;

    /**
     * Return cache tags
     *
     * @return array
     */
    public function getCacheTags(): array
    {
        return $this->cacheTags;
    }

    /**
     * Add Cache Tags
     *
     * @param array $cacheTags
     * @return void
     */
    public function addCacheTags(array $cacheTags): void
    {
        $this->cacheTags = array_unique(array_merge($this->cacheTags, $cacheTags));
    }

    /**
     * Return if it's valid to cache the response
     *
     * @return bool
     */
    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    /**
     * Set HTTP full page cache validity
     *
     * @param bool $cacheable
     */
    public function setCacheValidity(bool $cacheable): void
    {
        $this->cacheable = $cacheable;
    }

    /**
     * Check if query is cacheable and we have a list of tags to populate
     *
     * @return bool
     */
    public function shouldPopulateCacheHeadersWithTags() : bool
    {
        $cacheTags = $this->getCacheTags();
        $isQueryCacheable = $this->isCacheable();

        return !empty($cacheTags) && $isQueryCacheable;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->cacheTags = [];
        $this->cacheable = true;
    }
}
