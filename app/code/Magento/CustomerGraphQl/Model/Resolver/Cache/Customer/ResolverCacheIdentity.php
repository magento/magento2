<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache\Customer;

use Magento\Customer\Model\Customer;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Cache\IdentityInterface;

/**
 * Identity for resolved Customer for resolver cache type
 */
class ResolverCacheIdentity implements IdentityInterface
{
    /**
     * @var string
     */
    private $cacheTag = Customer::ENTITY;

    /**
     * @inheritdoc
     */
    public function getIdentities($resolvedData, ?array $parentResolvedData = null): array
    {
        return empty($resolvedData['model']->getId()) ?
            [] : [sprintf('%s_%s', $this->cacheTag, $resolvedData['model']->getId())];
    }
}
