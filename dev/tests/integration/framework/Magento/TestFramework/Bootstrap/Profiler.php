<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Bootstrap of the application profiler
 */
namespace Magento\TestFramework\Bootstrap;

class Profiler
{
    /**
     * Profiler driver instance
     *
     * @var \Magento\Framework\Profiler\Driver\Standard
     */
    protected $_driver;

    /**
     * Whether a profiler driver has been already registered or not
     *
     * @var bool
     */
    protected $_isDriverRegistered = false;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Profiler\Driver\Standard $driver
     */
    public function __construct(\Magento\Framework\Profiler\Driver\Standard $driver)
    {
        $this->_driver = $driver;
    }

    /**
     * Register profiler driver to involve it into the results processing
     */
    protected function _registerDriver()
    {
        if (!$this->_isDriverRegistered) {
            $this->_isDriverRegistered = true;
            \Magento\Framework\Profiler::add($this->_driver);
        }
    }

    /**
     * Register file-based profiling
     *
     * @param string $profilerOutputFile
     */
    public function registerFileProfiler($profilerOutputFile)
    {
        $this->_registerDriver();
        $this->_driver->registerOutput(
            new \Magento\Framework\Profiler\Driver\Standard\Output\Csvfile(['filePath' => $profilerOutputFile])
        );
    }

    /**
     * Register profiler with Bamboo-friendly output format
     *
     * @param string $profilerOutputFile
     * @param string $profilerMetricsFile
     */
    public function registerBambooProfiler($profilerOutputFile, $profilerMetricsFile)
    {
        $this->_registerDriver();
        $this->_driver->registerOutput(
            new \Magento\TestFramework\Profiler\OutputBamboo(
                ['filePath' => $profilerOutputFile, 'metrics' => require $profilerMetricsFile]
            )
        );
    }
}
