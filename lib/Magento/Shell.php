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
 * @package     Magento_Shell
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento;

/**
 * Shell command line wrapper encapsulates command execution and arguments escaping
 */
class Shell
{
    /**
     * Logger instance
     *
     * @var \Zend_Log
     */
    protected $_logger;

    /**
     * Operation system info
     *
     * @var OSInfo
     */
    protected $_osInfo;

    /**
     * @param OSInfo $osInfo
     * @param \Zend_Log $logger Logger instance to be used to log commands and their output
     */
    public function __construct(OSInfo $osInfo, \Zend_Log $logger = null)
    {
        $this->_logger = $logger;
        $this->_osInfo = $osInfo;
    }

    /**
     * Execute a command through the command line, passing properly escaped arguments, and return its output
     *
     * @param string $command Command with optional argument markers '%s'
     * @param array $arguments Argument values to substitute markers with
     * @return string Output of an executed command
     * @throws \Magento\Exception if a command returns non-zero exit code
     */
    public function execute($command, array $arguments = array())
    {
        $arguments = array_map('escapeshellarg', $arguments);
        $command = preg_replace('/\s?\||$/', ' 2>&1$0', $command);
        // Output errors to STDOUT instead of STDERR
        $command = vsprintf($command, $arguments);
        $this->_log($command);
        exec($command, $output, $exitCode);
        $output = implode(PHP_EOL, $output);
        $this->_log($output);
        if ($exitCode) {
            $commandError = new \Exception($output, $exitCode);
            throw new \Magento\Exception("Command `{$command}` returned non-zero exit code.", 0, $commandError);
        }
        return $output;
    }

    /**
     * Run external command in background
     *
     * @param string $command
     * @return void
     * @throws \Magento\Exception
     */
    public function executeInBackground($command)
    {
        if ($this->_osInfo->isWindows()) {
            $command = 'start /B "magento background task" ' . $command;
        } else {
            $command .= ' > /dev/null 2>1 &';
        }
        pclose(popen($command, 'r'));
    }

    /**
     * Log a message, if a logger is specified
     *
     * @param string $message
     * @return void
     */
    protected function _log($message)
    {
        if ($this->_logger) {
            $this->_logger->log($message, \Zend_Log::INFO);
        }
    }
}
