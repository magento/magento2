<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache\KeyFactorProvider;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\Cache\KeyFactorProviderInterface;

/**
 * Provides logged-in status as a factor to use in the cache key for resolver cache.
 */
class IsLoggedIn implements KeyFactorProviderInterface
{
    const NAME = "IS_LOGGED_IN";

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
        return $context->getExtensionAttributes()->getIsCustomer() ? "true" : "false";
    }
}
