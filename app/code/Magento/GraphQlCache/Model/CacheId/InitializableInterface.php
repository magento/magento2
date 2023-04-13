<?php
/*
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCache\Model\CacheId;

use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Interface for factors that should go into calculating the X-Magento-Cache-Id value used as a cache key
 */
interface InitializableInterface
{
    /**
     * Initialize state from previous resolver data and query context.
     *
     * @param array $resolvedData
     * @param ContextInterface $context
     * @return void
     */
    public function initialize(array $resolvedData, ContextInterface $context): void;
}
