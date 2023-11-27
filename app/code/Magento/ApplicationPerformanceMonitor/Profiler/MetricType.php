<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler;

/**
 * Enum for which type of metric
 */
class MetricType
{
    public const OTHER = "Other";
    public const SECONDSELAPSEDFLOAT = "SecondsElapsedFloat";
    public const UNIXTIMESTAMPFLOAT = "UnixTimestampFloat";
    public const MEMORYSIZEINT = "MemorySizeInt";
}
