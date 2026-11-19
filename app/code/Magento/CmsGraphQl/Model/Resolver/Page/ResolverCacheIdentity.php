<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page;
use Magento\CmsGraphQl\Model\Resolver\DataProvider\Page as PageDataProvider;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Cache\IdentityInterface;

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
     * @inheritdoc
     */
    public function getIdentities($resolvedData, ?array $parentResolvedData = null): array
    {
        if (empty($resolvedData[PageInterface::PAGE_ID])) {
            return [];
        }
        return array_merge(
            [sprintf('%s_%s', $this->cacheTag, $resolvedData[PageInterface::PAGE_ID])],
            $resolvedData[PageDataProvider::WIDGET_IDENTITIES] ?? []
        );
    }
}
