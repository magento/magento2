<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite;

/**
 * Pool of custom URL resolvers.
 */
class CustomUrlResolver implements CustomUrlResolverInterface
{
    /**
     * @var CustomUrlResolverInterface[]
     */
    private $urlResolvers;

    /**
     * @param CustomUrlResolverInterface[] $urlResolvers
     */
    public function __construct(array $urlResolvers = [])
    {
        $this->urlResolvers = $urlResolvers;
    }

    /**
     * @inheritdoc
     */
    public function resolveUrl($urlKey): ?string
    {
        foreach ($this->urlResolvers as $urlResolver) {
            $url = $urlResolver->resolveUrl($urlKey);
            if ($url !== null) {
                return $url;
            }
        }
        return null;
    }
}
