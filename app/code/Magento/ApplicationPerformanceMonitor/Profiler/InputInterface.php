<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler;

use Magento\Framework\AppInterface as Application;

/**
 * Interface for adding additional information.
 */
interface InputInterface
{
    /**
     * Input for other information
     *
     * @param Application $application
     * @return array
     */
    public function doInput(Application $application) : array;
}
