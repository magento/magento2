<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Plugin;

use Magento\ApplicationPerformanceMonitor\Profiler\Profiler;
use Magento\Framework\AppInterface as Application;
use Magento\Framework\App\ResponseInterface;

/**
 * Plugin that uses profiler to get performance metrics from Application
 */
class ApplicationPerformanceMonitor
{
    /**
     * @param Profiler $profiler
     */
    public function __construct(private Profiler $profiler)
    {
    }

    /**
     * Plugin that uses profiler to get performance metrics for application
     *
     * @param Application $subject
     * @param callable $proceed
     * @return ResponseInterface
     */
    public function aroundLaunch(
        Application $subject,
        callable $proceed,
    ) : ResponseInterface {
        if (!$this->profiler->isEnabled()) {
            return $proceed();
        }
        $returnValue = null;
        $this->profiler->doProfile(
            function () use ($proceed, &$returnValue) {
                $returnValue = $proceed();
            },
            $subject
        );
        return $returnValue;
    }
}
