<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Plugin;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Append id-specific tag to the list of tags for invalidation when global CMS page tag is present.
 * Magento\Cms\Model\Page::cleanModelCache overzealously purges all cache types for CMS page
 * after entity modification by providing the global tag. Since CMS page GraphQL responses can only contain
 * single entities, no two cms page identities can coexist in a GraphQL response.
 * Magento\CmsGraphQl\Model\Resolver\Page\BuiltInIdentity does not provide global cms_p tag in order to avoid a global
 * purge of the cache.  However, the specific entity being modified does need to be invalidated.  This plugin appends
 * the id-specific tag in order to invalidate just the single entity present in GraphQL Resolver cache type.
 */
class AppendIdSpecificTagToList
{
    /**
     * Append id-specific tag to the list of tags
     *
     * @param PageInterface $subject
     * @param array|false $tagsResult
     * @return array|false
     */
    public function afterGetCacheTags(PageInterface $subject, $tagsResult)
    {
        if (!is_array($tagsResult)) {
            return $tagsResult;
        }

        $hasGlobalCmsPageTag = in_array(Page::CACHE_TAG, $tagsResult);

        if ($hasGlobalCmsPageTag && $subject instanceof IdentityInterface) {
            $tagsResult = array_merge($tagsResult, $subject->getIdentities());
        }

        return $tagsResult;
    }
}
