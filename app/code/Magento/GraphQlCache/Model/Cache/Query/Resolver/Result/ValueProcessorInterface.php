<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;

interface ValueProcessorInterface
{
    public const VALUE_PROCESSOR_REFERENCE_KEY = 'value_processor_reference_key';

    public function postProcessCachedValue(ResolverInterface $resolver, ?string $cacheKey, &$value): void;

    public function preProcessParentResolverValue(&$value): void;
}
