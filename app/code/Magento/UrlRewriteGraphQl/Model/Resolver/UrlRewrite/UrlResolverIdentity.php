<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Get tags from url rewrite entities
 */
class UrlResolverIdentity implements IdentityInterface
{
    /**
     * @var IdentityInterface[]
     */
    private $urlResolverIdentities = [];

    /**
     * @param IdentityInterface[] $urlResolverIdentities
     */
    public function __construct(
        array $urlResolverIdentities
    ) {
        $this->urlResolverIdentities = $urlResolverIdentities;
    }

    /**
     * Get tags for the corespondent url type
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        if (isset($resolvedData['type']) && isset($this->urlResolverIdentities[strtolower($resolvedData['type'])])) {
            $ids = $this->urlResolverIdentities[strtolower($resolvedData['type'])]
                ->getIdentities($resolvedData);
        }
        return $ids;
    }
}
