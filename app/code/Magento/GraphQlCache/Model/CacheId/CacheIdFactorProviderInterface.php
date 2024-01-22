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
interface CacheIdFactorProviderInterface
{
    /**
     * Name of the cache key
     *
     * @return string
     */
    public function getFactorName(): string;

    /**
     * Runtime value that should be used to store the result under
     *
     * @param ContextInterface $context
     * @return string
     */
    public function getFactorValue(ContextInterface $context): string;
}
