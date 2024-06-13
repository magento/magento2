<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Cache\Tag;

/**
 * Invalidation tags generator
 *
 * @api
 * @since 100.1.3
 */
interface StrategyInterface
{
    /**
     * Return invalidation tags for specified object
     *
     * @param object $object
     * @throws \InvalidArgumentException
     * @return array
     * @since 100.1.3
     */
    public function getTags($object);
}
