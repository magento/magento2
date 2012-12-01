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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shell command line wrapper encapsulates command execution and arguments escaping
 */
class Magento_Shell
{
    /**
     * Verbosity of command execution - whether command output is printed to the standard output or not
     *
     * @var bool
     */
    protected $_isVerbose;

    /**
     * Constructor
     *
     * @param bool $isVerbose Whether command output is printed to the standard output or not
     */
    public function __construct($isVerbose = false)
    {
        $this->_isVerbose = $isVerbose;
    }

    /**
     * Set verbosity
     *
     * @param bool $isVerbose
     * @return Magento_Shell
     */
    public function setVerbose($isVerbose)
    {
        $this->_isVerbose = $isVerbose;
        return $this;
    }

    /**
     * Get verbosity
     *
     * @return bool
     */
    public function getVerbose()
    {
        return $this->_isVerbose;
    }

    /**
     * Execute a command through the command line, passing properly escaped arguments, and return its output
     *
     * @param string $command Command with optional argument markers '%s'
     * @param array $arguments Argument values to substitute markers with
     * @return string Output of an executed command
     * @throws Magento_Exception if a command returns non-zero exit code
     */
    public function execute($command, array $arguments = array())
    {
        $arguments = array_map('escapeshellarg', $arguments);
        $command = vsprintf("$command 2>&1", $arguments); // Output errors to STDOUT instead of STDERR
        if ($this->_isVerbose) {
            echo $command . PHP_EOL;
        }
        exec($command, $output, $exitCode);
        $output = implode(PHP_EOL, $output);
        if ($this->_isVerbose) {
            echo $output . PHP_EOL;
        }
        if ($exitCode) {
            $commandError = new Exception($output, $exitCode);
            throw new Magento_Exception("Command `$command` returned non-zero exit code.", 0, $commandError);
        }
        return $output;
    }
}
