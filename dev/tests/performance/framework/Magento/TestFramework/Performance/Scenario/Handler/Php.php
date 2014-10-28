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
 * Handler for performance testing scenarios in format of PHP console scripts
 */
namespace Magento\TestFramework\Performance\Scenario\Handler;

class Php implements \Magento\TestFramework\Performance\Scenario\HandlerInterface
{
    /**
     * @var \Magento\Framework\Shell
     */
    protected $_shell;

    /**
     * @var bool
     */
    protected $_validateExecutable;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Shell $shell
     * @param bool $validateExecutable
     */
    public function __construct(\Magento\Framework\Shell $shell, $validateExecutable = true)
    {
        $this->_shell = $shell;
        $this->_validateExecutable = $validateExecutable;
    }

    /**
     * Validate whether scenario executable is available in the environment
     */
    protected function _validateScenarioExecutable()
    {
        if ($this->_validateExecutable) {
            $this->_validateExecutable = false;
            // validate only once
            $this->_shell->execute('php --version');
        }
    }

    /**
     * Run scenario and optionally write results to report file
     *
     * @param \Magento\TestFramework\Performance\Scenario $scenario
     * @param string|null $reportFile Report file to write results to, NULL disables report creation
     * @throws \Magento\Framework\Exception
     * @throws \Magento\TestFramework\Performance\Scenario\FailureException
     *
     * @todo Implement execution in concurrent threads defined by the "users" scenario argument
     */
    public function run(\Magento\TestFramework\Performance\Scenario $scenario, $reportFile = null)
    {
        $this->_validateScenarioExecutable();

        $scenarioArguments = $scenario->getArguments();
        $reportRows = array();
        for ($i = 0; $i < $scenarioArguments[\Magento\TestFramework\Performance\Scenario::ARG_LOOPS]; $i++) {
            $oneReportRow = $this->_executeScenario($scenario);
            $reportRows[] = $oneReportRow;
        }
        if ($reportFile) {
            $this->_writeReport($reportRows, $reportFile);
        }
        $reportErrors = $this->_getReportErrors($reportRows);
        if ($reportErrors) {
            throw new \Magento\TestFramework\Performance\Scenario\FailureException(
                $scenario,
                implode(PHP_EOL, $reportErrors)
            );
        }
    }

    /**
     * Execute scenario and return measurement results
     *
     * @param \Magento\TestFramework\Performance\Scenario $scenario
     * @return array
     */
    protected function _executeScenario(\Magento\TestFramework\Performance\Scenario $scenario)
    {
        list($scenarioCmd, $scenarioCmdArgs) = $this->_buildScenarioCmd($scenario);
        $result = array(
            'title' => $scenario->getTitle(),
            'timestamp' => time(),
            'success' => true,
            'time' => null,
            'exit_code' => 0,
            'output' => ''
        );
        $executionTime = microtime(true);
        try {
            $result['output'] = $this->_shell->execute($scenarioCmd, $scenarioCmdArgs);
        } catch (\Magento\Framework\Exception $e) {
            $result['success'] = false;
            $result['exit_code'] = $e->getPrevious()->getCode();
            $result['output'] = $e->getPrevious()->getMessage();
        }
        $executionTime = microtime(true) - $executionTime;
        $executionTime *= 1000;
        // second -> millisecond
        $result['time'] = (int)round($executionTime);
        return $result;
    }

    /**
     * Build and return scenario execution command and arguments for it, compatible with the getopt() "long options"
     * @link http://www.php.net/getopt
     *
     * @param \Magento\TestFramework\Performance\Scenario $scenario
     * @return array
     */
    protected function _buildScenarioCmd(\Magento\TestFramework\Performance\Scenario $scenario)
    {
        $command = 'php -f %s --';
        $arguments = array($scenario->getFile());
        foreach ($scenario->getArguments() as $paramName => $paramValue) {
            $command .= " --{$paramName} %s";
            $arguments[] = $paramValue;
        }
        return array($command, $arguments);
    }

    /**
     * Write report into file in Apache JMeter's JTL format
     * @link http://wiki.apache.org/jmeter/JtlTestLog
     *
     * @param array $reportRows
     * @param string $reportFile
     */
    protected function _writeReport(array $reportRows, $reportFile)
    {
        $xml = array();
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<testResults version="1.2">';
        foreach ($reportRows as $index => $oneReportRow) {
            $xml[] = '<httpSample' .
                ' t="' .
                $oneReportRow['time'] .
                '"' .
                ' lt="0"' .
                ' ts="' .
                $oneReportRow['timestamp'] .
                '"' .
                ' s="' .
                ($oneReportRow['success'] ? 'true' : 'false') .
                '"' .
                ' lb="' .
                $oneReportRow['title'] .
                '"' .
                ' rc="' .
                $oneReportRow['exit_code'] .
                '"' .
                ' rm=""' .
                ' tn="Sample ' .
                ($index +
                1) .
                '"' .
                ' dt="text"' .
                '/>';
        }
        $xml[] = '</testResults>';
        file_put_contents($reportFile, implode(PHP_EOL, $xml));
    }

    /**
     * Retrieve error messages from the report
     *
     * @param array $reportRows
     * @return array
     */
    protected function _getReportErrors(array $reportRows)
    {
        $result = array();
        foreach ($reportRows as $oneReportRow) {
            if (!$oneReportRow['success']) {
                $result[] = $oneReportRow['output'];
            }
        }
        return $result;
    }
}
