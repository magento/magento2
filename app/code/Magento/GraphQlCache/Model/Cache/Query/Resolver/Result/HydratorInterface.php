<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result;

/**
 * Hydrator interface for resolver data.
 */
interface HydratorInterface
{
    /**
     * Hydrate resolved data.
     *
     * @param array $resolverData
     * @return void
     */
    public function hydrate(array &$resolverData): void;
}
