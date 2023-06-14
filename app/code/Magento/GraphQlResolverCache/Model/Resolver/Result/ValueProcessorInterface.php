<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Value processor for resolved value and parent resolver value.
 */
interface ValueProcessorInterface
{
    /**
     * Key for data processing reference.
     */
    public const VALUE_PROCESSING_REFERENCE_KEY = 'value_processing_reference_key';

    /**
     * Process the cached value after loading from cache for the given resolver.
     *
     * @param ResolveInfo $info
     * @param ResolverInterface $resolver
     * @param string $cacheKey
     * @param array|mixed $value
     * @return void
     */
    public function processCachedValueAfterLoad(
        ResolveInfo $info,
        ResolverInterface $resolver,
        string $cacheKey,
        &$value
    ): void;

    /**
     * Preprocess parent resolver resolved array for currently executed array-element resolver.
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
