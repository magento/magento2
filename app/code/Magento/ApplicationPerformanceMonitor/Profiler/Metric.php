<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler;

/**
 * A single metric. Type is currently either MEMORY or TIME.
 * This class is an immutable data object.
 */
class Metric
{
    /**
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @param bool $verbose
     */
    public function __construct(
        private readonly string $type,
        private readonly string $name,
        private readonly mixed $value,
        private readonly bool $verbose,
    ) {
    }

    /**
     * Gets type of metric
     *
     * @return int|string
     */
    public function getType(): string|int
    {
        return $this->type;
    }

    /**
     * Gets a name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets a value
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Checks if verbose
     *
     * @return bool
     */
    public function isVerbose(): bool
    {
        return $this->verbose;
    }
}
