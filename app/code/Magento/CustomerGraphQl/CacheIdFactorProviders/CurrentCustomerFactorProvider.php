<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\CacheIdFactorProviders;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\KeyFactorProvider;

/**
 * Provides logged-in customer id as a factor to use in the cache key.
 */
class CurrentCustomerFactorProvider implements KeyFactorProvider\ParentResolverResultFactoredInterface
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
    public function getFactorValueForParentResolvedData(ContextInterface $context, ?array $parentResolverData): string
    {
        return $this->getFactorValue($context);
    }

    /**
     * @inheritDoc
     */
    public function getFactorValue(ContextInterface $context): string
    {
        return (string)$context->getUserId();
    }
}
