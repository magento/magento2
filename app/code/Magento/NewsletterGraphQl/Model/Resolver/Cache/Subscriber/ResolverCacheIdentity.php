<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewsletterGraphQl\Model\Resolver\Cache\Subscriber;

use Magento\GraphQlResolverCache\Model\Resolver\Result\Cache\IdentityInterface;

/**
 * Identity for resolved Customer subscription status for resolver cache type
 */
class ResolverCacheIdentity implements IdentityInterface
{
    /**
     * @var string
     */
    private $cacheTag = 'SUBSCRIBER';

    /**
     * @inheritdoc
     */
    public function getIdentities($resolvedData, ?array $parentResolvedData = null): array
    {
        return empty($parentResolvedData['model']->getId()) ?
            [] : [sprintf('%s_%s', $this->cacheTag, $parentResolvedData['model']->getId())];
    }
}
