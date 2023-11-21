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
    public const Other = "Other";
    public const SecondsElapsedFloat = "SecondsElapsedFloat";
    public const UnixTimestampFloat = "UnixTimestampFloat";
    public const MemorySizeInt = "MemorySizeInt";
}
