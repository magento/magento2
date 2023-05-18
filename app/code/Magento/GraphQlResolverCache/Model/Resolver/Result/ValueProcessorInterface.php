<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Value processor for resolved value and parent resolver value.
 */
interface ValueProcessorInterface
{
    /**
     * Process the cached value after loading from cache for the given resolver.
     *
     * @param ResolverInterface $resolver
     * @param string $cacheKey
     * @param array|mixed $value
     * @return void
     */
    public function processCachedValueAfterLoad(ResolverInterface $resolver, string $cacheKey, &$value): void;

    /**
     * Preprocess parent resolver resolved array for currently executed resolver.
     *
     * @param array $value
     * @return void
     */
    public function preProcessParentValue(array &$value): void;

    /**
     * Preprocess value before saving to cache for the given resolver.
     *
     * @param ResolverInterface $resolver
     * @param array|mixed $value
     * @return void
     */
    public function preProcessValueBeforeCacheSave(ResolverInterface $resolver, &$value): void;
}
