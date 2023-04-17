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
//    /**
//     * Initialize strategy for the provided resolver.
//     *
//     * @param ResolverInterface $resolver
//     * @return void
//     */
//    public function initForResolver(ResolverInterface $resolver): void;

    /**
     * Get cache id calculator for the given resolver.
     *
     * @param ResolverInterface $resolver
     * @param ContextInterface $context
     * @param array|null $parentResult
     * @return KeyCalculator
     */
    public function getForResolver(ResolverInterface $resolver): KeyCalculator;

//    /**
//     * Reinitialize state of the factor providers for the given resolver from the previous resolver data.
//     *
//     * @param ResolverInterface $resolver
//     * @param array|null $result
//     * @param ContextInterface $context
//     * @return void
//     */
//    public function actualize(ResolverInterface $resolver, ?array $result, ContextInterface $context): void;
}
