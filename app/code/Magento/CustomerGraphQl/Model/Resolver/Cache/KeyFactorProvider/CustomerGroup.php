<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache\KeyFactorProvider;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\Cache\KeyFactorProviderInterface;

/**
 * Provides customer group as a factor to use in the cache key for resolver cache.
 */
class CustomerGroup implements KeyFactorProviderInterface
{
    private const NAME = "CUSTOMER_GROUP";

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
    public function getFactorValue(ContextInterface $context, ?array $parentResolverData = null): string
    {
        return (string)($context->getExtensionAttributes()->getCustomerGroupId()
            ?? GroupInterface::NOT_LOGGED_IN_ID);
    }
}
