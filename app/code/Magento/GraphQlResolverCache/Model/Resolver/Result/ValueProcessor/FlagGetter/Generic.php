<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessor\FlagGetter;

use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessorInterface;

class Generic implements FlagGetterInterface
{
    public function setFlagOnValue(&$value, string $flagValue): void
    {
        $value[ValueProcessorInterface::VALUE_HYDRATION_REFERENCE_KEY] = $flagValue;
    }

    public function getFlagFromValue(&$value): ?string
    {
        return $value[ValueProcessorInterface::VALUE_HYDRATION_REFERENCE_KEY] ?? null;
    }
}
