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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MemoryUsageTest extends PHPUnit_Framework_TestCase
{
    /**
     * Number of application reinitialization iterations to be conducted by tests
     */
    const APP_REINITIALIZATION_LOOPS = 20;

    /**
     * Test that application reinitialization produces no memory leaks
     */
    public function testAppReinitializationNoMemoryLeak()
    {
        $this->_deallocateUnusedMemory();
        $actualMemoryUsage = $this->_getRealMemoryUsage();
        for ($i = 0; $i < self::APP_REINITIALIZATION_LOOPS; $i++) {
            Magento_Test_Bootstrap::getInstance()->reinitialize();
            $this->_deallocateUnusedMemory();
        }
        $actualMemoryUsage = $this->_getRealMemoryUsage() - $actualMemoryUsage;
        $this->assertLessThanOrEqual($this->_getAllowedMemoryUsage(), $actualMemoryUsage, sprintf(
            "Application reinitialization causes the memory leak of %u bytes per %u iterations.",
            $actualMemoryUsage,
            self::APP_REINITIALIZATION_LOOPS
        ));
    }

    /**
     * Force to deallocate no longer used memory
     */
    protected function _deallocateUnusedMemory()
    {
        gc_collect_cycles();
    }

    /**
     * Retrieve the allowed memory usage in bytes, depending on the environment
     *
     * @return int
     */
    protected function _getAllowedMemoryUsage()
    {
        // Memory usage limits should not be further increased, corresponding memory leaks have to be fixed instead!
        if ($this->_isWindowsOs()) {
            return $this->_convertToBytes('1M');
        }
        return 0;
    }

    /**
     * Whether the operating system belongs to the Windows family
     *
     * @link http://php.net/manual/en/function.php-uname.php
     * @return bool
     */
    protected function _isWindowsOs()
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }

    /**
     * Retrieve the effective memory usage of the current process
     *
     * memory_get_usage() cannot be used because of the bug
     * @link https://bugs.php.net/bug.php?id=62467
     *
     * @return int Memory usage in bytes
     */
    protected function _getRealMemoryUsage()
    {
        $pid = getmypid();
        $shell = new Magento_Shell();
        if ($this->_isWindowsOs()) {
            $result = $this->_getWinProcessMemoryUsage($shell, $pid);
        } else {
            $result = $this->_getUnixProcessMemoryUsage($shell, $pid);
        }
        return $result;
    }

    /**
     * Retrieve the current process' memory usage using Unix command line interface
     *
     * @param Magento_Shell $shell
     * @param int $pid
     * @return int Memory usage in bytes
     */
    protected function _getUnixProcessMemoryUsage(Magento_Shell $shell, $pid)
    {
        /**
         * @link http://linux.die.net/man/1/top
         *
         * Output format invariant:
         *   PID USER    PR  NI  VIRT  RES  SHR S %CPU %MEM    TIME+  COMMAND
         * 12345 root    20   0  215m  36m  10m S   98  0.5   0:32.96 php
         */
        $output = $shell->execute('top -p %s -n 1 -b | grep PID -A 1', array($pid));

        $output = preg_split('/\n+/', $output, -1, PREG_SPLIT_NO_EMPTY);
        $keys = preg_split('/\s+/', $output[0], -1, PREG_SPLIT_NO_EMPTY);
        $values = preg_split('/\s+/', $output[1], -1, PREG_SPLIT_NO_EMPTY);
        $stats = array_combine($keys, $values);

        $result = $stats['RES']; // resident set size, the non-swapped physical memory

        if (is_numeric($result)) {
            $result .= 'k'; // kilobytes by default
        }

        return $this->_convertToBytes($result);
    }

    /**
     * Retrieve the current process' memory usage using Windows command line interface
     *
     * @param Magento_Shell $shell
     * @param int $pid
     * @return int Memory usage in bytes
     */
    protected function _getWinProcessMemoryUsage(Magento_Shell $shell, $pid)
    {
        /**
         * @link http://technet.microsoft.com/en-us/library/bb491010.aspx
         *
         * Output format invariant:
         * "Image Name","PID","Session Name","Session#","Mem Usage"
         * "php.exe","12345","N/A","0","26,321 K"
         */
        $output = $shell->execute('tasklist /fi %s /fo CSV', array("PID eq $pid"));

        /** @link http://www.php.net/manual/en/wrappers.data.php */
        $csvStream = 'data://text/plain;base64,' . base64_encode($output);
        $csvHandle = fopen($csvStream, 'r');
        $keys = fgetcsv($csvHandle);
        $values = fgetcsv($csvHandle);
        fclose($csvHandle);
        $stats = array_combine($keys, $values);

        $result = $stats['Mem Usage'];

        return $this->_convertToBytes($result);
    }

    /**
     * Convert a number optionally followed by the unit symbol (B, K, M, G, etc.) to bytes
     *
     * @param string $number String representation of a number
     * @return int
     * @throws InvalidArgumentException
     */
    protected function _convertToBytes($number)
    {
        $number = str_replace(array(',', ' '), '', $number);
        $number = strtoupper($number);
        $units = 'BKMGTPEZY';
        if (!preg_match("/^(\d+(?:\.\d+)?)([$units]?)$/", $number, $matches)) {
            throw new InvalidArgumentException("Number format '$number' is not recognized.");
        }
        $result = (float)$matches[1];
        $unitSymbol = $matches[2];
        if ($unitSymbol) {
            $result *= pow(1024, strpos($units, $unitSymbol));
        }
        return (int)$result;
    }
}
