<?php
/**
 * Standard profiler driver that uses outputs for displaying profiling results.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Driver;

use Magento\Framework\Profiler\Driver\Standard\Output\Factory as OutputFactory;
use Magento\Framework\Profiler\Driver\Standard\OutputInterface;
use Magento\Framework\Profiler\Driver\Standard\Stat;
use Magento\Framework\Profiler\DriverInterface;

class Standard implements DriverInterface
{
    /**
     * Storage for timers statistics
     *
     * @var Stat
     */
    protected $_stat;

    /**
     * List of profiler driver outputs
     *
     * @var OutputInterface[]
     */
    protected $_outputs = [];

    /**
     * Constructor
     *
     * @param array|null $config
     */
    public function __construct(array $config = null)
    {
        $this->_initOutputs($config);
        $this->_initStat($config);
        register_shutdown_function([$this, 'display']);
    }

    /**
     * Init outputs by configuration
     *
     * @param array|null $config
     * @return void
     */
    protected function _initOutputs(array $config = null)
    {
        if (!$config) {
            return;
        }

        $outputFactory = $this->_getOutputFactory($config);
        foreach ($this->_getOutputConfigs($config) as $code => $outputConfig) {
            $outputConfig = $this->_parseOutputConfig($outputConfig);
            if (false === $outputConfig) {
                continue;
            }
            if (!isset($outputConfig['type']) && !is_numeric($code)) {
                $outputConfig['type'] = $code;
            }
            if (!isset($outputConfig['baseDir']) && isset($config['baseDir'])) {
                $outputConfig['baseDir'] = $config['baseDir'];
            }
            $this->registerOutput($outputFactory->create($outputConfig));
        }
    }

    /**
     * Parses output config
     *
     * @param mixed $outputConfig
     * @return array|bool
     */
    protected function _parseOutputConfig($outputConfig)
    {
        $result = false;
        if (is_array($outputConfig)) {
            $result = $outputConfig;
        } elseif (is_scalar($outputConfig) && $outputConfig) {
            if (is_numeric($outputConfig)) {
                $result = [];
            } else {
                $result = ['type' => $outputConfig];
            }
        }
        return $result;
    }

    /**
     * Get output configs
     *
     * @param array $config
     * @return array
     */
    protected function _getOutputConfigs(array $config = null)
    {
        $result = [];
        if (isset($config['outputs'])) {
            $result = $config['outputs'];
        } elseif (isset($config['output'])) {
            $result[] = $config['output'];
        }
        return $result;
    }

    /**
     * Gets output factory from configuration or create new one
     *
     * @param array|null $config
     * @return OutputFactory
     */
    protected function _getOutputFactory(array $config = null)
    {
        if (isset($config['outputFactory']) && $config['outputFactory'] instanceof OutputFactory) {
            $result = $config['outputFactory'];
        } else {
            $result = new OutputFactory();
        }
        return $result;
    }

    /**
     * Init timers statistics object from configuration or create new one
     *
     * @param array $config|null
     * @return void
     */
    protected function _initStat(array $config = null)
    {
        if (isset($config['stat']) && $config['stat'] instanceof Stat) {
            $this->_stat = $config['stat'];
        } else {
            $this->_stat = new Stat();
        }
    }

    /**
     * Clear collected statistics for specified timer or for whole profiler if timer id is omitted
     *
     * @param string|null $timerId
     * @return void
     */
    public function clear($timerId = null)
    {
        $this->_stat->clear($timerId);
    }

    /**
     * Start collecting statistics for specified timer
     *
     * @param string $timerId
     * @param array|null $tags
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function start($timerId, array $tags = null)
    {
        $this->_stat->start($timerId, microtime(true), memory_get_usage(true), memory_get_usage());
    }

    /**
     * Stop recording statistics for specified timer.
     *
     * @param string $timerId
     * @return void
     */
    public function stop($timerId)
    {
        $this->_stat->stop($timerId, microtime(true), memory_get_usage(true), memory_get_usage());
    }

    /**
     * Register profiler output instance to display profiling result at the end of execution
     *
     * @param OutputInterface $output
     * @return void
     */
    public function registerOutput(OutputInterface $output)
    {
        $this->_outputs[] = $output;
    }

    /**
     * Display collected statistics with registered outputs
     *
     * @return void
     */
    public function display()
    {
        if (\Magento\Framework\Profiler::isEnabled()) {
            foreach ($this->_outputs as $output) {
                $output->display($this->_stat);
            }
        }
    }
}
