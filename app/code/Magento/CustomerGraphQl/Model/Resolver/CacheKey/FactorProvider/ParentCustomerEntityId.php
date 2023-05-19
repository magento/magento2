<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\CacheKey\FactorProvider;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\ParentValueFactorProviderInterface;

/**
 * Provides customer id from the parent resolved value as a factor to use in the cache key for resolver cache.
 */
class ParentCustomerEntityId implements ParentValueFactorProviderInterface
{
    /**
     * Factor name.
     */
    private const NAME = "PARENT_ENTITY_CUSTOMER_ID";

    /**
     * @inheritdoc
     */
    public function getFactorName(): string
    {
        return static::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getFactorValue(ContextInterface $context, ?array $parentResolverData = null): string
    {
        return (string)$parentResolverData['model']->getId();
    }
}
