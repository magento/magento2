<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\CacheIdFactorProviders;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\CacheId\CacheIdFactorProviderInterface;

/**
 * Provides currency code as a factor to use in the cache id
 */
class CurrencyProvider implements CacheIdFactorProviderInterface
{
    const NAME = "CURRENCY";

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
        return (string)$context->getExtensionAttributes()->getStore()->getCurrentCurrencyCode();
    }
}
