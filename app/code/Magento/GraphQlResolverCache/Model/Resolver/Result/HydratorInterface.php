<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

/**
 * Hydrator interface for resolver data.
 */
interface HydratorInterface
{
    /**
     * Hydration of the resolved data may be needed before passing to child resolver.
     *
     * @param array $resolverData
     * @return void
     */
    public function hydrate(array &$resolverData): void;

    /**
     * Pre-hydration may occur right after cache read on the whole cached record.
     *
     * Data structure corresponds to dehydrated result in DehydratorInterface.
     *
     * @param array $resolverData
     * @return void
     */
    public function prehydrate(array &$resolverData): void;
}
