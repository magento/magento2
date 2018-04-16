<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCache\Model\FlushCacheByProductIds;

use Magento\CacheInvalidate\Model\PurgeCache;

/**
 * Purge varnish cache by specified tags.
 */
class PurgeVarnishCacheByTags
{
    /**
     * @var PurgeCache
     */
    private $varnishCache;

    /**
     * @param PurgeCache $varnishCache
     */
    public function __construct(PurgeCache $varnishCache)
    {
        $this->varnishCache = $varnishCache;
    }

    /**
     * @param array $bareTags
     * @return void
     */
    public function execute(array $bareTags)
    {
        $tags = [];
        $pattern = "((^|,)%s(,|$))";
        foreach ($bareTags as $tag) {
            $tags[] = sprintf($pattern, $tag);
        }
        $this->varnishCache->sendPurgeRequest(implode('|', array_unique($tags)));
    }
}
