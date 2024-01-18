<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessor\FlagSetter;

use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessorInterface;

/**
 * List of objects value flag setter/unsetter.
 */
class ListOfObjects implements FlagSetterInterface
{
    /**
     * @inheritdoc
     */
    public function setFlagOnValue(&$value, string $flagValue): void
    {
        foreach (array_keys($value) as $key) {
            $value[$key][ValueProcessorInterface::VALUE_PROCESSING_REFERENCE_KEY] = [
                'cacheKey' => $flagValue,
                'index' => $key
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function unsetFlagFromValue(&$value): void
    {
        foreach (array_keys($value) as $key) {
            unset($value[$key][ValueProcessorInterface::VALUE_PROCESSING_REFERENCE_KEY]);
        }
    }
}
