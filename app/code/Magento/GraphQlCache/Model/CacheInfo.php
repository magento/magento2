<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model;

use Magento\Eav\Model\Attribute\Data\Boolean;

/**
 * CacheInfo object is a registry for collecting cache related info and tags of all entities.
 */
class CacheInfo
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
     * Returns if its valid to cache the response
     *
     * @return bool
     */
    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    /**
     * Sets cache validity
     *
     * @param bool $cacheable
     */
    public function setCacheValidity(bool $cacheable): void
    {
        $this->cacheable = $cacheable;
    }
}
