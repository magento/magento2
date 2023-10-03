<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Plugin;

use Magento\ApplicationPerformanceMonitor\Profiler\Profiler;
use Magento\Framework\AppInterface as Application;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\App\Response\HttpInterface;
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
     * @param HttpRequestInterface|null $request
     * @return HttpInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) XXXXXXXXXXXXX TODO remove this line if no longer needed
     */
    public function aroundLaunch(
        Application $subject,
        callable $proceed,
    ) : ResponseInterface {
//        $previousRequestCount = $this->previousRequestCount;
//        $this->previousRequestCount++;
//        $profiler = $this->profilerFactory->create();
        if (!$this->profiler->isEnabled()) {
            return $proceed();
        }
        $returnValue = null;
        $this->profiler->doProfile(
            function () use ($proceed, &$returnValue) {
                $returnValue = $proceed();
            },
//            function () use ($previousRequestCount, $subject) {
//                $information = [];
////                $information['subject'] = 'ApplicationServer Application::launch';
////                if ($request instanceof \Magento\Framework\HTTP\PhpEnvironment\Request) {
////                    $information['httpMethod'] = $request->getMethod();
////                    if ($request->isPost()) {
////                        $information['requestContentLength'] = strlen($request->getContent());
////                    }
////                }
//                return $information;
//            }
            $subject
        );
        return $returnValue;
    }
}
