<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model;

/**
 * CacheableQuery should be used as a singleton for collecting cache related info and tags of all entities.
 */
class CacheableQuery
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
        $this->cacheTags = array_merge($this->cacheTags, $cacheTags);
    }

    /**
     * Return if its valid to cache the response
     *
     * @return bool
     */
    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    /**
     * Set cache validity
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
        $isQueryCaheable = $this->isCacheable();
        return !empty($cacheTags) && $isQueryCaheable;
    }
}
