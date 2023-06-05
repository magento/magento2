<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\CacheKey\FactorProvider;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\ParentValue\PlainValueFactorInterface;

/**
 * Provides customer id from the parent resolved value as a factor to use in the cache key for resolver cache.
 */
class ParentCustomerEntityId implements PlainValueFactorInterface
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
    public function getFactorValue(ContextInterface $context, ?array $parentValue = null): string
    {
        return (string)$parentValue['model_id'];
    }
}
