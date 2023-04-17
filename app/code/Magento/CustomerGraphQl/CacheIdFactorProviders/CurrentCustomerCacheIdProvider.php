<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\CacheIdFactorProviders;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\ResolverDependentFactorProviderInterface;

/**
 * Provides logged-in customer id as a factor to use in the cache id.
 */
class CurrentCustomerCacheIdProvider implements ResolverDependentFactorProviderInterface
{
    /**
     * Factor name.
     */
    private const NAME = "CUSTOMER_ID";

    /**
     * @inheritdoc
     */
    public function getFactorName(): string
    {
        return static::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getFactorValueForResolvedData(ContextInterface $context, ?array $resolvedData): string
    {
        $customerId = $resolvedData['model_id'];
        $currentUserId = $context->getUserId();
        if ($currentUserId != $customerId) {
            throw new \Exception("User context is different from the one resolved.");
        }
        return (string)$currentUserId;
    }

    /**
     * @inheritDoc
     */
    public function getFactorValue(ContextInterface $context): string
    {
        throw new \Exception("Must call getFactorValueForResolvedData() instead.");
    }
}
