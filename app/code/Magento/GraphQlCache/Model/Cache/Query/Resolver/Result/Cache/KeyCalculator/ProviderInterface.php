<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\KeyCalculator;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\KeyCalculator;

/**
 * Interface for custom resolver cache id providers strategy.
 */
interface ProviderInterface
{
    /**
     * Get cache id calculator for the given resolver.
     *
     * @param ResolverInterface $resolver
     * @return KeyCalculator
     */
    public function getKeyCalculatorForResolver(ResolverInterface $resolver): KeyCalculator;
}
