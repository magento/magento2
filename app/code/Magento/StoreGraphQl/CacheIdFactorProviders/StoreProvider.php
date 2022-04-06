<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\CacheIdFactorProviders;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\CacheId\CacheIdFactorProviderInterface;

/**
 * Provides store code as a factor to use in the cache id
 */
class StoreProvider implements CacheIdFactorProviderInterface
{
    const NAME = "STORE";

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
        return (string)$context->getExtensionAttributes()->getStore()->getCode();
    }
}
