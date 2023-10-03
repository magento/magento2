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
    const TYPE_OTHER = 1;
    const TYPE_SECONDS_ELAPSED_FLOAT = 1;
    const TYPE_UNIX_TIMESTAMP_FLOAT = 2;
    const TYPE_MEMORY_SIZE_INT = 3;

    /**
     * @param int $type
     * @param string $name
     * @param mixed $value
     * @param bool $verbose
     */
    public function __construct(private int $type, private string $name, private mixed $value, private bool $verbose)
    {
    }

    /**
     * @return int
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
