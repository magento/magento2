<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache;

use Magento\Customer\Model\Customer;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved Customer for resolver cache type
 */
class CustomerResolverCacheIdentity implements IdentityInterface
{
    /**
     * @var string
     */
    private $cacheTag = Customer::ENTITY;

    /**
     * Get customer ID from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        return empty($resolvedData['model']->getId()) ?
            [] : [sprintf('%s_%s', $this->cacheTag, $resolvedData['model']->getId())];
    }
}
