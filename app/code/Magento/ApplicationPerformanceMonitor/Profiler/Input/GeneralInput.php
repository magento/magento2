<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler\Input;

use Magento\ApplicationPerformanceMonitor\Profiler\InputInterface;
use Magento\Framework\AppInterface;

/**
 * Adds applicationClass based on the current application
 */
class GeneralInput implements InputInterface
{
    /**
     * @inheritDoc
     */
    public function doInput(AppInterface $application) : array
    {
        return ['applicationClass'=> get_class($application)];
    }
}
