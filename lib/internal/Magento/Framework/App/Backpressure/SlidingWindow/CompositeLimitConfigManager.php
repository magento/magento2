<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\Exception\RuntimeException;

/**
 * Delegates finding configs for different requests types to other instances
 */
class CompositeLimitConfigManager implements LimitConfigManagerInterface
{
    /**
     * @var LimitConfigManagerInterface[]
     */
    private array $configs;

    /**
     * @param LimitConfigManagerInterface[] $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * @inheritDoc
     *
     * @throws RuntimeException
     */
    public function readLimit(ContextInterface $context): LimitConfig
    {
        if (isset($this->configs[$context->getTypeId()])) {
            return $this->configs[$context->getTypeId()]->readLimit($context);
        }

        throw new RuntimeException(
            __(
                'Failed to find config manager for "%typeId".',
                [ 'typeId' => $context->getTypeId()]
            )
        );
    }
}
