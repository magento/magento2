<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\CacheIdFactorProviders;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\CacheId\InitializableCacheIdFactorProviderInterface;

/**
 * Provides logged-in customer id as a factor to use in the cache id.
 */
class CurrentCustomerCacheIdProvider implements InitializableCacheIdFactorProviderInterface
{
    /**
     * Factor name.
     */
    const NAME = "CUSTOMER_ID";

    /**
     * @var string
     */
    private $factorValue = '';

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
        return $this->factorValue;
    }

    /**
     * @inheritdoc
     */
    public function initialize(array $resolvedData, ContextInterface $context): void
    {
        $this->factorValue = ((string)$context->getUserId() ?: '');
    }
}
