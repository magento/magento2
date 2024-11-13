<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessor\FlagSetter;

use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessorInterface;

/**
 * Single entity object flag value setter/unsetter.
 */
class SingleObject implements FlagSetterInterface
{
    /**
     * @inheritdoc
     */
    public function setFlagOnValue(&$value, string $flagValue): void
    {
        $value[ValueProcessorInterface::VALUE_PROCESSING_REFERENCE_KEY] = [
            'cacheKey' => $flagValue,
            'index' => 0
        ];
    }

    /**
     * @inheritdoc
     */
    public function unsetFlagFromValue(&$value): void
    {
        unset($value[ValueProcessorInterface::VALUE_PROCESSING_REFERENCE_KEY]);
    }
}
