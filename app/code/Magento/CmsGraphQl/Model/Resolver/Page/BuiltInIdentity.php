<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved CMS page for built-in resolver cache type
 *
 * Magento\Cms\Model\Page::cleanModelCache overzealously purges all cache types for CMS page
 * after modification of any CMS page by providing the global tag. Since CMS page GraphQL responses can only contain
 * single entities, no two CMS page-specific tag assignments can coexist in a GraphQL resolver cache entry.
 * Therefore, purging the entire CMS page-related GraphQL resolver cache on a single CMS page update is overkill for
 * this cache type.  This class purposely does not provide global cms_p tag in order to avoid a global purge of the
 * CMS page-related GraphQL resolver cache.
 */
class BuiltInIdentity implements IdentityInterface
{
    /**
     * @var string
     */
    private $cacheTag = \Magento\Cms\Model\Page::CACHE_TAG;

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
