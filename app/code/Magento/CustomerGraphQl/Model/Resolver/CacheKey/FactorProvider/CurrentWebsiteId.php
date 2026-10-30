<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\CacheKey\FactorProvider;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\GenericFactorProviderInterface;

/**
 * Provides current website ID as a factor for the resolver cache key.
 * Prevents cross-website cache hits when customer accounts are website-scoped.
 */
class CurrentWebsiteId implements GenericFactorProviderInterface
{
    /**
     * Factor name.
     */
    private const NAME = 'CURRENT_WEBSITE_ID';

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
        return (string)$context->getExtensionAttributes()->getStore()->getWebsiteId();
    }
}
