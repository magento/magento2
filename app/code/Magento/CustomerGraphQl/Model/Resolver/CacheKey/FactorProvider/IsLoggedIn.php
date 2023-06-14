<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\CacheKey\FactorProvider;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\GenericFactorProviderInterface;

/**
 * Provides logged-in status as a factor to use in the cache key for resolver cache.
 */
class IsLoggedIn implements GenericFactorProviderInterface
{
    private const NAME = "IS_LOGGED_IN";

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
    public function getFactorValue(ContextInterface $context): string
    {
        return $context->getExtensionAttributes()->getIsCustomer() ? "true" : "false";
    }
}
