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
 * @package     performance_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Handler for performance testing scenarios in format of PHP console scripts
 */
class Magento_Performance_Scenario_Handler_Php implements Magento_Performance_Scenario_HandlerInterface
{
    /**
     * @var Magento_Shell
     */
    protected $_shell;

    /**
     * Constructor
     *
     * @param Magento_Shell $shell
     */
    public function __construct(Magento_Shell $shell)
    {
        $this->_shell = $shell;
        $this->_validateScenarioExecutable();
    }

    /**
     * Validate whether scenario executable is available in the environment
     */
    protected function _validateScenarioExecutable()
    {
        $this->_shell->execute('php --version');
    }

    /**
     * Run scenario and optionally write results to report file
     *
     * @param string $scenarioFile
     * @param Magento_Performance_Scenario_Arguments $scenarioArguments
     * @param string|null $reportFile Report file to write results to, NULL disables report creation
     * @return bool Whether handler was able to process scenario
     *
     * @todo Implement execution in concurrent threads defined by the "users" scenario argument
     */
    public function run($scenarioFile, Magento_Performance_Scenario_Arguments $scenarioArguments, $reportFile = null)
    {
        if (pathinfo($scenarioFile, PATHINFO_EXTENSION) != 'php') {
            return false;
        }
        $reportRows = array();
        for ($i = 0; $i < $scenarioArguments->getLoops(); $i++) {
            $oneReportRow = $this->_executeScenario($scenarioFile, $scenarioArguments);
            $reportRows[] = $oneReportRow;
        }
        if ($reportFile) {
            $this->_writeReport($reportRows, $reportFile);
        }
        $this->_verifyReport($reportRows);
        return true;
    }

    /**
     * Execute scenario file and return measurement results
     *
     * @param string $scenarioFile
     * @param Traversable $scenarioArgs
     * @return array
     */
    protected function _executeScenario($scenarioFile, Traversable $scenarioArgs)
    {
        list($scenarioCmd, $scenarioCmdArgs) = $this->_buildScenarioCmd($scenarioFile, $scenarioArgs);
        $result = array(
            'scenario'  => $scenarioFile,
            'timestamp' => time(),
            'success'   => true,
            'time'      => null,
            'exit_code' => 0,
            'output'    => '',
        );
        $executionTime = microtime(true);
        try {
            $result['output'] = $this->_shell->execute($scenarioCmd, $scenarioCmdArgs);
        } catch (Magento_Exception $e) {
            $result['success']   = false;
            $result['exit_code'] = $e->getPrevious()->getCode();
            $result['output']    = $e->getPrevious()->getMessage();
        }
        $executionTime = (microtime(true) - $executionTime);
        $executionTime *= 1000; // second -> millisecond
        $result['time'] = (int)round($executionTime);
        return $result;
    }

    /**
     * Build and return scenario execution command and arguments for it
     *
     * @param string $scenarioFile
     * @param Traversable $scenarioArgs
     * @return array
     */
    protected function _buildScenarioCmd($scenarioFile, Traversable $scenarioArgs)
    {
        $command = 'php -f %s --';
        $arguments = array($scenarioFile);
        foreach ($scenarioArgs as $paramName => $paramValue) {
            $command .= " --$paramName=%s";
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
            $xml[] = '<httpSample'
                . ' t="' . $oneReportRow['time'] . '"'
                . ' lt="0"'
                . ' ts="' . $oneReportRow['timestamp'] . '"'
                . ' s="' . ($oneReportRow['success'] ? 'true' : 'false') . '"'
                . ' lb="' . $oneReportRow['scenario'] . '"'
                . ' rc="' . $oneReportRow['exit_code'] . '"'
                . ' rm=""'
                . ' tn="Sample ' . ($index + 1) . '"'
                . ' dt="text"'
                . '/>';
        }
        $xml[] = '</testResults>';
        file_put_contents($reportFile, implode(PHP_EOL, $xml));
    }

    /**
     * Verify that report does not contain failures
     *
     * @param array $reportRows
     * @throws Magento_Performance_Scenario_FailureException
     */
    protected function _verifyReport(array $reportRows)
    {
        $failureMessages = array();
        foreach ($reportRows as $oneReportRow) {
            if (!$oneReportRow['success']) {
                $failureMessages[] = $oneReportRow['output'];
            }
        }
        if ($failureMessages) {
            throw new Magento_Performance_Scenario_FailureException(implode(PHP_EOL, $failureMessages));
        }
    }
}
