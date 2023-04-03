<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved CMS page for resolver cache type
 */
class ResolverCacheIdentity implements IdentityInterface
{
    /**
     * @var string
     */
    private $cacheTag = Page::CACHE_TAG;

    /**
     * Get page ID from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        return empty($resolvedData[PageInterface::PAGE_ID]) ?
            [] : [sprintf('%s_%s', $this->cacheTag, $resolvedData[PageInterface::PAGE_ID])];
    }
}
