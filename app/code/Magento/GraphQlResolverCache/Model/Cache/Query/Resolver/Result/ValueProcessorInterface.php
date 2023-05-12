<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Data processor for resolved value.
 */
interface ValueProcessorInterface
{
    /**
     * Key for data processing reference.
     */
    public const VALUE_HYDRATION_REFERENCE_KEY = 'value_hydration_reference_key';

    /**
     *  Process the cached value after loading from cache.
     *
     * @param ResolverInterface $resolver
     * @param string $cacheKey
     * @param array|null $value
     * @return void
     */
    public function processCachedValueAfterLoad(ResolverInterface $resolver, string $cacheKey, ?array &$value): void;

    /**
     * Preprocess parent resolver resolved value.
     *
     * @param array|null $value
     * @return void
     */
    public function preProcessParentResolverValue(?array &$value): void;

    /**
     * Preprocess value before saving to cache.
     *
     * @param ResolverInterface $resolver
     * @param array|null $value
     * @return void
     */
    public function preProcessValueBeforeCacheSave(ResolverInterface $resolver, ?array &$value): void;
}
