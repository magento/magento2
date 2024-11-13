<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessor\FlagGetter;

use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessorInterface;

/**
 * Single entity object structure flag getter.
 */
class SingleObject implements FlagGetterInterface
{
    /**
     * @inheritdoc
     */
    public function getFlagFromValue($value): ?array
    {
        return $value[ValueProcessorInterface::VALUE_PROCESSING_REFERENCE_KEY] ?? null;
    }
}
