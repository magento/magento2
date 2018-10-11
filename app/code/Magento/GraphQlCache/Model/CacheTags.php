<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model;

/**
 * Cache tags object is a registry for collecting cache tags of all entities used in the GraphQL query response.
 */
class CacheTags
{
    /**
     * @var string[]
     */
    private $cacheTags = [];

    /**
     * @return string[]
     */
    public function getCacheTags(): array
    {
        return $this->cacheTags;
    }

    /**
     * @param string[] $tags
     * @return void
     */
    public function addCacheTags(array $cacheTags): void
    {
        $this->cacheTags = array_merge($this->cacheTags, $cacheTags);
    }
}
