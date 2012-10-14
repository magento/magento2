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
 * Handler for tracking and collecting scenario execution results
 */
class Magento_Performance_Scenario_Handler_Statistics implements Magento_Performance_Scenario_HandlerInterface
{
    /**
     * Result of the successful scenario execution
     */
    const RESULT_SUCCESS = null;

    /**
     * @var Magento_Performance_Scenario_HandlerInterface
     */
    protected $_handler;

    /**
     * @var array
     */
    protected $_executedScenarios = array();

    /**
     * @var callable
     */
    protected $_onScenarioFirstRun;

    /**
     * @var callable
     */
    protected $_onScenarioFailure;

    /**
     * Constructor
     *
     * @param Magento_Performance_Scenario_HandlerInterface $handlerInstance
     */
    public function __construct(Magento_Performance_Scenario_HandlerInterface $handlerInstance)
    {
        $this->_handler = $handlerInstance;
    }

    /**
     * Run scenario and optionally write results to report file
     *
     * @param string $scenarioFile
     * @param Magento_Performance_Scenario_Arguments $scenarioArguments
     * @param string|null $reportFile Report file to write results to, NULL disables report creation
     * @return bool Whether handler was able to process scenario
     */
    public function run($scenarioFile, Magento_Performance_Scenario_Arguments $scenarioArguments, $reportFile = null)
    {
        if (!array_key_exists($scenarioFile, $this->_executedScenarios)) {
            $this->_notifyScenarioFirstRun($scenarioFile);
        }
        try {
            $result = $this->_handler->run($scenarioFile, $scenarioArguments, $reportFile);
            if ($result) {
                $this->_recordScenarioResult($scenarioFile, self::RESULT_SUCCESS);
            }
            return $result;
        } catch (Magento_Performance_Scenario_FailureException $scenarioFailure) {
            $this->_recordScenarioResult($scenarioFile, $scenarioFailure);
            $this->_notifyScenarioFailure($scenarioFile, $scenarioFailure);
            return true;
        }
    }

    /**
     * Retrieve scenario failures
     *
     * @return array
     */
    public function getFailures()
    {
        $failures = array();
        foreach ($this->_executedScenarios as $scenarioFile => $scenarioResult) {
            if ($scenarioResult !== self::RESULT_SUCCESS) {
                $failures[$scenarioFile] = $scenarioResult;
            }
        }
        return $failures;
    }

    /**
     * Set callback for scenario first run event
     *
     * @param callable $callback
     */
    public function onScenarioFirstRun($callback)
    {
        $this->_onScenarioFirstRun = $callback;
    }

    /**
     * Set callback for scenario failure event
     *
     * @param callable $callback
     */
    public function onScenarioFailure($callback)
    {
        $this->_onScenarioFailure = $callback;
    }

    /**
     * Notify about scenario first run event
     *
     * @param string $scenarioFile
     */
    protected function _notifyScenarioFirstRun($scenarioFile)
    {
        if ($this->_onScenarioFirstRun) {
            call_user_func($this->_onScenarioFirstRun, $scenarioFile);
        }
    }

    /**
     * Notify about scenario failure event
     *
     * @param string $scenarioFile
     * @param Magento_Performance_Scenario_FailureException $failure
     */
    protected function _notifyScenarioFailure($scenarioFile, Magento_Performance_Scenario_FailureException $failure)
    {
        if ($this->_onScenarioFailure) {
            call_user_func($this->_onScenarioFailure, $scenarioFile, $failure);
        }
    }

    /**
     * Store result of scenario execution
     *
     * @param string $scenarioFile
     * @param Magento_Performance_Scenario_FailureException|null $result
     */
    protected function _recordScenarioResult($scenarioFile,
        Magento_Performance_Scenario_FailureException $result = null
    ) {
        if (!array_key_exists($scenarioFile, $this->_executedScenarios)) {
            $this->_executedScenarios[$scenarioFile] = $result;
        }
    }
}
