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
     * Hydrates resolved data before passing to child resolver.
     *
     * @param array $resolverData
     * @return void
     */
    public function hydrate(array &$resolverData): void;
}
