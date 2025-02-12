<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewriteGraphQl\Model\Resolver\UrlRewrite;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Get ids from cms page url rewrite
 */
class CmsUrlResolverIdentity implements IdentityInterface
{
    /** @var string */
    private $cacheTag = \Magento\Cms\Model\Page::CACHE_TAG;

    /**
     * Get identities cache ID from a url rewrite entities
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        $id = $resolvedData['id'] ?? $resolvedData['page_id'] ?? null;
        if (isset($id)) {
            $selectedCacheTag = $this->cacheTag;
            $ids =  [$selectedCacheTag, sprintf('%s_%s', $selectedCacheTag, $id)];
        }
        return $ids;
    }
}
