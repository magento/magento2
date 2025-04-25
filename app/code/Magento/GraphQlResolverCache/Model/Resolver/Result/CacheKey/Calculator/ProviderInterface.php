<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator;

/**
 * Interface for cache key calculator provider.
 */
interface ProviderInterface
{
    /**
     * Get cache key calculator for the given resolver.
     *
     * @param ResolverInterface $resolver
     * @return Calculator
     *
     * @throws \InvalidArgumentException
     */
    public function getKeyCalculatorForResolver(ResolverInterface $resolver): Calculator;
}
