<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            new \Magento\Framework\Profiler\Driver\Standard\Output\Csvfile(array('filePath' => $profilerOutputFile))
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
                array('filePath' => $profilerOutputFile, 'metrics' => require $profilerMetricsFile)
            )
        );
    }
}
