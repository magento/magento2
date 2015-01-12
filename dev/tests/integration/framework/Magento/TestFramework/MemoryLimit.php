<?php
/**
 * A tool for limiting allowed memory usage and memory leaks
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework;

class MemoryLimit
{
    /**
     * @var \Magento\TestFramework\Helper\Memory
     */
    private $_helper;

    /**
     * @var int
     */
    private $_memCap = 0;

    /**
     * @var int
     */
    private $_leakCap = 0;

    /**
     * Initialize with the values
     *
     * @param string $memCap
     * @param string $leakCap
     * @param \Magento\TestFramework\Helper\Memory $helper
     * @throws \InvalidArgumentException
     */
    public function __construct($memCap, $leakCap, \Magento\TestFramework\Helper\Memory $helper)
    {
        $this->_memCap = $memCap ? $helper->convertToBytes($memCap) : 0;
        $this->_leakCap = $leakCap ? $helper->convertToBytes($leakCap) : 0;
        $this->_helper = $helper;
    }

    /**
     * Get a header printout
     *
     * @return string
     */
    public static function printHeader()
    {
        return PHP_EOL . '=== Memory Usage System Stats ===' . PHP_EOL;
    }

    /**
     * Get statistics printout
     *
     * @return string
     */
    public function printStats()
    {
        list($usage, $leak) = $this->_getUsage();
        $result = [];

        $msg = sprintf(
            "Memory usage (OS):\t%s (%.2F%% of %s reported by PHP",
            $this->_toMb($usage),
            100 * $usage / ($usage - $leak),
            $this->_toMb($usage - $leak)
        );
        $percentMsg = '%.2F%% of configured %s limit';
        if ($this->_memCap) {
            $msg .= ', ' . sprintf($percentMsg, 100 * $usage / $this->_memCap, $this->_toMb($this->_memCap));
        }
        $result[] = "{$msg})";

        $msg = sprintf("Estimated memory leak:\t%s (%.2F%% of used memory", $this->_toMb($leak), 100 * $leak / $usage);
        if ($this->_leakCap) {
            $msg .= ', ' . sprintf($percentMsg, 100 * $leak / $this->_leakCap, $this->_toMb($this->_leakCap));
        }
        $result[] = "{$msg})";

        return implode(PHP_EOL, $result) . PHP_EOL;
    }

    /**
     * Convert bytes to mebibytes (2^20)
     *
     * @param int $bytes
     * @return string
     */
    private function _toMb($bytes)
    {
        return sprintf('%.2FM', $bytes / (1024 * 1024));
    }

    /**
     * Raise error if memory usage breaks configured thresholds
     *
     * @return null
     * @throws \LogicException
     */
    public function validateUsage()
    {
        if (!$this->_memCap && !$this->_leakCap) {
            return null;
        }
        list($usage, $leak) = $this->_getUsage();
        if ($this->_memCap && $usage >= $this->_memCap) {
            throw new \LogicException(
                "Memory limit of {$this->_toMb($this->_memCap)} ({$this->_memCap} bytes) has been reached."
            );
        }
        if ($this->_leakCap && $leak >= $this->_leakCap) {
            throw new \LogicException(
                "Estimated memory leak limit of {$this->_toMb(
                    $this->_leakCap
                )}" . " ({$this->_leakCap} bytes) has been reached."
            );
        }
    }

    /**
     * Usage/leak getter sub-routine
     *
     * @return array
     */
    private function _getUsage()
    {
        $usage = $this->_helper->getRealMemoryUsage();
        return [$usage, $usage - memory_get_usage(true)];
    }
}
