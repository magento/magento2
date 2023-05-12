<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver\Cache\KeyFactorProvider;

use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\Cache\KeyFactorProviderInterface;

/**
 * Provides store code as a factor to use in the resolver cache key.
 */
class Store implements KeyFactorProviderInterface
{
    private const NAME = "STORE";

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
        return $context->getExtensionAttributes()->getStore()->getCode();
    }
}
