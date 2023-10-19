<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler;

/**
 * A single metric.  Type is currently either MEMORY or TIME.
 * This class is an immutable data object.
 */
class Metric
{
    /**
     * @param int $type
     * @param string $name
     * @param mixed $value
     * @param bool $verbose
     */
    public function __construct(
        private readonly MetricType $type,
        private readonly string $name,
        private readonly mixed $value,
        private readonly bool $verbose,
    ) {
    }

    /**
     * @return MetricType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isVerbose()
    {
        return $this->verbose;
    }
}
