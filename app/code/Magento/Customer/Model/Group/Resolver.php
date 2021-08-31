<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Group;

use Magento\Customer\Model\ResourceModel\Group\Resolver as ResolverResource;

/**
 * Lightweight service for getting current customer group based on customer id
 */
class Resolver
{
    /**
     * @var ResolverResource
     */
    private $resolverResource;

    /**
     * @param ResolverResource $resolverResource
     */
    public function __construct(ResolverResource $resolverResource)
    {
        $this->resolverResource = $resolverResource;
    }

    /**
     * Return customer group id
     *
     * @param int $customerId
     * @return int|null
     */
    public function resolve(int $customerId) : ?int
    {
        return $this->resolverResource->resolve($customerId);
    }
}
