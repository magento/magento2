<?php
/**
 * Helper for determining system memory usage
 *
 * Uses OS tools to provide accurate information about factual memory consumption.
 * The PHP standard functions may return incorrect information because the process itself may have leaks.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Test_Helper_Memory
{
    /**
     * Prefixes to specify unit of measure for memory amount
     *
     * Warning: it is important to maintain the exact order of letters in this literal,
     * as it is used to convert string with units to bytes
     */
    const MEMORY_UNITS = 'BKMGTPE';

    /**
     * @var Magento_Shell
     */
    private $_shell;

    /**
     * Inject dependencies
     *
     * @param Magento_Shell $shell
     */
    public function __construct(Magento_Shell $shell)
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
        if (self::isWindowsOs()) {
            $result = $this->getWinProcessMemoryUsage($pid);
        } else {
            $result = $this->getUnixProcessMemoryUsage($pid);
        }
        return $result;
    }

    /**
     * Retrieve the current process' memory usage using Unix command line interface
     *
     * @link http://linux.die.net/man/1/top
     * @param int $pid
     * @return int Memory usage in bytes
     */
    public function getUnixProcessMemoryUsage($pid)
    {
        $output = $this->_shell->execute('top -p %s -n 1 -b | grep PID -A 1', array($pid));

        $output = preg_split('/\n+/', $output, -1, PREG_SPLIT_NO_EMPTY);
        $keys = preg_split('/\s+/', $output[0], -1, PREG_SPLIT_NO_EMPTY);
        $values = preg_split('/\s+/', $output[1], -1, PREG_SPLIT_NO_EMPTY);
        $stats = array_combine($keys, $values);

        $result = $stats['RES']; // resident set size, the non-swapped physical memory

        if (is_numeric($result)) {
            $result .= 'k'; // kilobytes by default
        }

        return self::convertToBytes($result);
    }

    /**
     * Retrieve the current process' memory usage using Windows command line interface
     *
     * @link http://technet.microsoft.com/en-us/library/bb491010.aspx
     * @param int $pid
     * @return int Memory usage in bytes
     */
    public function getWinProcessMemoryUsage($pid)
    {
        $output = $this->_shell->execute('tasklist /fi %s /fo CSV /nh', array("PID eq $pid"));

        /** @link http://www.php.net/manual/en/wrappers.data.php */
        $csvStream = 'data://text/plain;base64,' . base64_encode($output);
        $csvHandle = fopen($csvStream, 'r');
        $stats = fgetcsv($csvHandle);
        fclose($csvHandle);

        $result = $stats[4];

        return self::convertToBytes($result);
    }

    /**
     * Whether the operating system belongs to the Windows family
     *
     * @link http://php.net/manual/en/function.php-uname.php
     * @return bool
     */
    public static function isWindowsOs()
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }

    /**
     * Convert a number optionally followed by the unit symbol (B, K, M, G, etc.) to bytes
     *
     * @param string $number String representation of a number
     * @return int
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    public static function convertToBytes($number)
    {
        $number = str_replace(array(',', ' '), '', $number);
        $number = strtoupper($number);
        if (!preg_match('/^(\d+(?:\.\d+)?)([' . self::MEMORY_UNITS . ']?)$/', $number, $matches)) {
            throw new InvalidArgumentException("Number format '$number' is not recognized.");
        }
        $result = (float)$matches[1];
        $unitSymbol = $matches[2];
        $pow = $unitSymbol ? strpos(self::MEMORY_UNITS, $unitSymbol) : 0;
        $is32Bit = PHP_INT_SIZE == 4;
        if ($is32Bit && $pow >= 4) {
            throw new OutOfBoundsException("A 32-bit system is unable to process such a number.");
        }
        if ($unitSymbol) {
            $result *= pow(1024, $pow);
        }
        return (int)$result;
    }
}
