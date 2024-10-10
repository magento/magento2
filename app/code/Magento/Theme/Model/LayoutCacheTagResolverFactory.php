<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Theme\Model;

use InvalidArgumentException;
use Magento\Framework\App\Cache\Tag\StrategyInterface;

/**
 * Creates strategies for layout cache
 */
class LayoutCacheTagResolverFactory
{
    /**
     * Construct
     *
     * @param array $cacheTagsResolvers
     */
    public function __construct(
        private readonly array $cacheTagsResolvers
    ) {
    }

    /**
     * Return tag resolver for specified object
     *
     * @param object $object
     * @return StrategyInterface|null
     */
    public function getStrategy(object $object): ?StrategyInterface
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('Provided argument is not an object');
        }

        $classHierarchy = array_merge(
            [get_class($object) => get_class($object)],
            class_parents($object),
            class_implements($object)
        );

        $result = array_intersect(array_keys($this->cacheTagsResolvers), $classHierarchy);
        if ($result) {
            return $this->cacheTagsResolvers[array_shift($result)];
        }
        return null;
    }
}
