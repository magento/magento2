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
 * @package     Magento
 * @subpackage  static_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract shell command for the static code inspection
 */
abstract class Inspection_CommandAbstract
{
    /**
     * @var string
     */
    protected $_reportFile;

    /**
     * @var int
     */
    protected $_lastExitCode;

    /**
     * @var string
     */
    protected $_lastOutput;

    /**
     * @var string
     */
    protected $_lastRunMessage;

    /**
     * Constructor
     *
     * @param string $reportFile Destination file to write inspection report to
     */
    public function __construct($reportFile)
    {
        $this->_reportFile = $reportFile;
    }

    /**
     * Build and execute the shell command
     *
     * @param array $whiteList Files/directories to be inspected
     * @param array $blackList Files/directories to be excluded from the inspection
     * @return bool
     */
    public function run(array $whiteList, array $blackList = array())
    {
        if (file_exists($this->_reportFile)) {
            unlink($this->_reportFile);
        }
        $shellCmd = $this->_buildShellCmd($whiteList, $blackList);
        $result = $this->_execShellCmd($shellCmd);
        $this->_generateLastRunMessage();
        return $result !== false;
    }

    /**
     * Whether the command can be ran on the current environment
     *
     * @return bool
     */
    public function canRun()
    {
        return ($this->_execShellCmd($this->_buildVersionShellCmd()) !== false);
    }

    /**
     * Retrieve the shell command version
     *
     * @return string|null
     */
    public function getVersion()
    {
        $versionOutput = $this->_execShellCmd($this->_buildVersionShellCmd());
        if (!$versionOutput) {
            return null;
        }
        return (preg_match('/[^\d]*([^\s]+)/', $versionOutput, $matches) ? $matches[1] : $versionOutput);
    }

    /**
     * Get path to the report file
     *
     * @return string
     */
    public function getReportFile()
    {
        return $this->_reportFile;
    }

    /**
     * Build the shell command that outputs the version
     *
     * @return string
     */
    abstract protected function _buildVersionShellCmd();

    /**
     * Build the valid shell command
     *
     * @param array $whiteList
     * @param array $blackList
     * @return string
     */
    abstract protected function _buildShellCmd($whiteList, $blackList);

    /**
     * Execute a shell command on the current environment and return its output or FALSE on failure
     *
     * @param string $shellCmd
     * @return string|false
     */
    protected function _execShellCmd($shellCmd)
    {
        $output = array();
        exec($shellCmd . ' 2>&1', $output, $this->_lastExitCode);
        $this->_lastOutput = implode(PHP_EOL, $output);
        return ($this->_lastExitCode === 0 ? $this->_lastOutput : false);
    }

    /**
     * Generate message about last execution result, prepared for output to a user
     *
     * @return Inspection_CommandAbstract
     */
    protected function _generateLastRunMessage()
    {
        if ($this->_lastExitCode === null) {
            $this->_lastRunMessage = "Nothing was executed.";
        } else if (!$this->_lastExitCode) {
            $this->_lastRunMessage = 'Success reported.';
        } else if (file_exists($this->_reportFile)) {
            $this->_lastRunMessage = "See detailed report in '{$this->_reportFile}'.";
        } else {
            $this->_lastRunMessage = 'Command-line tool reports: ' . $this->_lastOutput;
        }
        return $this;
    }

    /**
     * Return message from the last run of the command
     *
     * @return string
     */
    public function getLastRunMessage()
    {
        return $this->_lastRunMessage;
    }
}
