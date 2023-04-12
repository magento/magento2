<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved Customer for resolver cache type
 */
class CustomerResolverCacheIdentity implements IdentityInterface
{
    /**
     * @var string
     */
    private $cacheTag = 'CUSTOMER';

    /**
     * Get page ID from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        return empty($resolvedData['model_id']) ?
            [] : [sprintf('%s_%s', $this->cacheTag, $resolvedData['model_id'])];
    }
}
