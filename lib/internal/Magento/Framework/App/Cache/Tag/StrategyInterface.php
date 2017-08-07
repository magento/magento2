<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache\Tag;

/**
 * Invalidation tags generator
 *
 * @api
 * @since 2.1.3
 */
interface StrategyInterface
{
    /**
     * Return invalidation tags for specified object
     *
     * @param object $object
     * @throws \InvalidArgumentException
     * @return array
     * @since 2.1.3
     */
    public function getTags($object);
}
