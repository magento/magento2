<?php
/**
 * Helper for determining system memory usage
 *
 * Uses OS tools to provide accurate information about factual memory consumption.
 * The PHP standard functions may return incorrect information because the process itself may have leaks.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Helper;

class Memory
{
    /**
     * Prefixes to specify unit of measure for memory amount
     *
     * Warning: it is important to maintain the exact order of letters in this literal,
     * as it is used to convert string with units to bytes
     */
    const MEMORY_UNITS = 'BKMGTPE';

    /**
     * @var \Magento\Framework\Shell
     */
    private $_shell;

    /**
     * Inject dependencies
     *
     * @param \Magento\Framework\Shell $shell
     */
    public function __construct(\Magento\Framework\Shell $shell)
    {
        $this->_shell = $shell;
    }

    /**
     * Retrieve the effective memory usage of the current process
     *
     * memory_get_usage() cannot be used because of the bug
     * @link https://bugs.php.net/bug.php?id=62467
     *
     * @return int Memory usage in bytes
     */
    public function getRealMemoryUsage()
    {
        $pid = getmypid();
        try {
            // try to use the Windows command line
            // some ports of Unix commands on Windows, such as MinGW, have limited capabilities and cannot be used
            $result = $this->_getWinProcessMemoryUsage($pid);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // fall back to the Unix command line
            $result = $this->_getUnixProcessMemoryUsage($pid);
        }
        return $result;
    }

    /**
     * Retrieve the current process' memory usage using Unix command line interface
     *
     * @link http://linux.die.net/man/1/ps
     * @param int $pid
     * @return int Memory usage in bytes
     */
    protected function _getUnixProcessMemoryUsage($pid)
    {
        // RSS - resident set size, the non-swapped physical memory
        $command = 'ps --pid %s --format rss --no-headers';
        if ($this->isMacOS()) {
            $command = 'ps -p %s -o rss=';
        }
        $output = $this->_shell->execute($command, [$pid]);
        $result = $output . 'k';
        // kilobytes
        return self::convertToBytes($result);
    }

    /**
     * Retrieve the current process' memory usage using Windows command line interface
     *
     * @link http://technet.microsoft.com/en-us/library/bb491010.aspx
     * @param int $pid
     * @return int Memory usage in bytes
     */
    protected function _getWinProcessMemoryUsage($pid)
    {
        $output = $this->_shell->execute('tasklist.exe /fi %s /fo CSV /nh', ["PID eq {$pid}"]);

        $arr = str_getcsv($output);
        $memory = $arr[4];
        return self::convertToBytes($memory);
    }

    /**
     * Convert a number optionally followed by the unit symbol (B, K, M, G, etc.) to bytes
     *
     * @param string $number String representation of a number
     * @return int
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     */
    public static function convertToBytes($number)
    {
        if (!preg_match('/^(.*\d)\h*(\D)$/', $number, $matches)) {
            throw new \InvalidArgumentException("Number format '{$number}' is not recognized.");
        }
        $unitSymbol = strtoupper($matches[2]);
        if (false === strpos(self::MEMORY_UNITS, $unitSymbol)) {
            throw new \InvalidArgumentException("The number '{$number}' has an unrecognized unit: '{$unitSymbol}'.");
        }
        $result = self::_convertToNumber($matches[1]);
        $pow = $unitSymbol ? strpos(self::MEMORY_UNITS, $unitSymbol) : 0;
        $is32Bit = PHP_INT_SIZE == 4;
        if ($is32Bit && $pow >= 4) {
            throw new \OutOfBoundsException("A 32-bit system is unable to process such a number.");
        }
        if ($unitSymbol) {
            $result *= pow(1024, $pow);
        }
        return (int)$result;
    }

    /**
     * Remove non-numeric characters in the string to cast it to a numeric value
     *
     * Incoming number can be presented in arbitrary format that depends on locale. We don't possess locale information.
     * So the best can be done is to treat number as an integer and eliminate delimiters.
     * Method will not behave correctly with non-integer numbers for the following reason:
     * - if value has more than one delimiter, such as in French notation: "1 234,56" -- then we can infer decimal part
     * - but the value has only one delimiter, such as "234,56", then it is impossible to know whether it is decimal
     *   separator or not. Only knowing the right format would allow this.
     *
     * @param $number
     * @return string
     * @throws \InvalidArgumentException
     */
    protected static function _convertToNumber($number)
    {
        preg_match_all('/(\D+)/', $number, $matches);
        if (count(array_unique($matches[0])) > 1) {
            throw new \InvalidArgumentException(
                "The number '{$number}' seems to have decimal part. Only integer numbers are supported."
            );
        }
        return preg_replace('/\D+/', '', $number);
    }

    /**
     * Whether the operating system belongs to the Mac family
     *
     * @link http://php.net/manual/en/function.php-uname.php
     * @return boolean
     */
    public static function isMacOs()
    {
        return strtoupper(PHP_OS) === 'DARWIN';
    }
}
