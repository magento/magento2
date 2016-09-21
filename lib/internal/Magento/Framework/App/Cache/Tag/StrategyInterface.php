<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache\Tag;

/**
 * Invalidation tags generator
 */
interface StrategyInterface
{
    /**
     * Return invalidation tags for specified object
     *
     * @param object $object
     * @throws \InvalidArgumentException
     * @return array
     */
    public function getTags($object);
}
