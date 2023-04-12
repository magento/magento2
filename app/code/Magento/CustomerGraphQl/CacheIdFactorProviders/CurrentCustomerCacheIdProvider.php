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
 * Provides logged-in status as a factor to use in the cache id
 */
class CurrentCustomerCacheIdProvider implements InitializableCacheIdFactorProviderInterface
{
    const NAME = "CUSTOMER_ID";

    /**
     * @var string|null
     */
    private $factorValue = null;

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
        return (string)$this->factorValue ?: ((string)$context->getUserId() ?: '');
    }

    /**
     * @param array $resolvedData
     * @return void
     */
    public function initFromResolvedData(array $resolvedData): void
    {
//        if (isset($resolvedData['model_id'])) {
//            $this->factorValue = $resolvedData['model_id'];
//        }
    }

    /**
     * @param ContextInterface $context
     * @return void
     */
    public function initFromContext(ContextInterface $context): void
    {
//        if ($context->getUserId()) {
//            $this->factorValue = (string)$context->getUserId();
//        }
    }
}
