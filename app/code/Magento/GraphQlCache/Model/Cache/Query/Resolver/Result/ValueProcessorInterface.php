<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Data processor for resolved value.
 */
interface ValueProcessorInterface
{
    /**
     * Key for data processing reference.
     */
    public const VALUE_PROCESSOR_REFERENCE_KEY = 'value_processor_reference_key';

    /**
     * Post process the cached value after loading from cache.
     *
     * @param ResolverInterface $resolver
     * @param string|null $cacheKey
     * @param array|null $value
     * @return void
     */
    public function postProcessCachedValue(ResolverInterface $resolver, ?string $cacheKey, ?array &$value): void;

    /**
     * Preprocess parent resolver resolved value.
     *
     * @param array|null $value
     * @return void
     */
    public function preProcessParentResolverValue(?array &$value): void;
}
